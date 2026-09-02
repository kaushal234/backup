<?php

declare(strict_types=1);

namespace LegacyBundle\DataProvider;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Sales\ExtranetUser;
use LegacyBundle\Entity\ServiceBulletin;
use LegacyBundle\Entity\ServiceBulletinLine;
use LegacyBundle\Security\ExtranetUserCustomerResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Item provider for a ServiceBulletin: keeps the default Doctrine item provider (so the
 * visibility restrictions of {@see \LegacyBundle\Doctrine\ORM\Extension\ServiceBulletinQueryExtension}
 * still apply), then restricts the exposed lines to the equipments an extranet user is allowed
 * to see.
 *
 * For an extranet user, only the lines whose EquipmentRecord (buyer/maintainer/endUser) belongs
 * to one of their accessible customers are kept. When a `customer` query parameter is provided
 * and accessible, the result is further narrowed down to that single customer; otherwise it
 * falls back to all accessible customers.
 */
final readonly class ServiceBulletinItemProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ItemProvider $itemProvider,
        private Security $security,
        private ExtranetUserCustomerResolver $customerResolver,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ServiceBulletin
    {
        $serviceBulletin = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$serviceBulletin instanceof ServiceBulletin) {
            return null;
        }

        $user = $this->security->getUser();

        // Internal users (People) and any non-extranet user keep the full set of lines.
        if (!$user instanceof ExtranetUser) {
            return $serviceBulletin;
        }

        $accessibleCustomerIds = $this->customerResolver->getAccessibleCustomerLegacyIds($user);
        $allowedCustomerIds = $this->resolveAllowedCustomerIds($accessibleCustomerIds);

        $serviceBulletin->lines = $serviceBulletin->lines->filter(
            static function (ServiceBulletinLine $line) use ($allowedCustomerIds): bool {
                $equipmentRecord = $line->equipmentRecord;

                return \in_array($equipmentRecord->buyer, $allowedCustomerIds, true)
                    || \in_array($equipmentRecord->maintainer, $allowedCustomerIds, true)
                    || \in_array($equipmentRecord->endUser, $allowedCustomerIds, true);
            }
        );

        return $serviceBulletin;
    }

    /**
     * @param list<int> $accessibleCustomerIds
     *
     * @return list<int>
     */
    private function resolveAllowedCustomerIds(array $accessibleCustomerIds): array
    {
        $requestedCustomer = $this->requestStack->getCurrentRequest()?->query->get('customer');

        // Narrow down to the requested (active) customer only when it is one the user can access,
        // never allowing an escalation to a non-accessible customer.
        if (null !== $requestedCustomer && ctype_digit((string) $requestedCustomer)) {
            $requestedCustomerId = (int) $requestedCustomer;

            if (\in_array($requestedCustomerId, $accessibleCustomerIds, true)) {
                return [$requestedCustomerId];
            }
        }

        return $accessibleCustomerIds;
    }
}
