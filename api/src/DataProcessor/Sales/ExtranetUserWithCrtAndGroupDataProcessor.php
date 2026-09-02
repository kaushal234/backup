<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Sales\ExtranetUserWithCrtInput;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\ExtranetUserGroup;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T
 */
final readonly class ExtranetUserWithCrtAndGroupDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private UserManager $userManager,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param ExtranetUserWithCrtInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (null !== $this->entityManager->getRepository(ExtranetUser::class)->findOneBy(['email' => $data->extranetUser->getEmail()])) {
            throw new BadRequestException($this->translator->trans('directory.extranet_user.errors.email_already_used', [], 'directory'));
        }

        $extranetUserGroupRepository = $this->entityManager->getRepository(ExtranetUserGroup::class);

        /** @var ExtranetUserGroup $extranetUserGroup */
        $extranetUserGroup = $extranetUserGroupRepository->findOneby(['name' => $data->groupName]);

        if (null === $extranetUserGroup) {
            throw new BadRequestException('A valid group name must be provided.');
        }

        $extranetUser = $data->extranetUser;
        $this->userManager->generatePassword($extranetUser);
        $extranetUser->getExtranetUserProfile()->customer = $data->customerRelationshipTeam->getCustomer();
        $extranetUser->getExtranetUserProfile()->companyName = $data->customerRelationshipTeam->getCustomer()->getName();

        $createdExtranetUser = $this->persistProcessor->process($extranetUser, $operation, $uriVariables, $context);

        $extranetUserAclRepository = $this->entityManager->getRepository(ExtranetUserAcl::class);

        $qb = $extranetUserAclRepository->createQueryBuilder('acl');
        $qb->join('acl.crt', 'crt')
            ->andwhere('crt.erpLocation = :erpLocation')
            ->andWhere('crt.customer = :customer')
            ->andWhere('acl.extranetUser = :extranetUser')
            ->andWhere('acl.extranetUserGroup = :extranetUserGroup')
            ->setParameter('erpLocation', $data->customerRelationshipTeam->getErpLocation())
            ->setParameter('customer', $data->customerRelationshipTeam->getCustomer())
            ->setParameter('extranetUser', $createdExtranetUser)
            ->setParameter('extranetUserGroup', $extranetUserGroup);

        $extranetUserAcl = $qb->getQuery()->getOneOrNullResult();

        if (null === $extranetUserAcl) {
            $extranetUserAcl = new ExtranetUserAcl();
            $extranetUserAcl
                ->setExtranetUser($createdExtranetUser)
                ->setExtranetUserGroup($extranetUserGroup)
                ->setCrt($data->customerRelationshipTeam)
            ;

            $this->entityManager->persist($extranetUserAcl);
            $this->entityManager->flush();
        }

        return $createdExtranetUser;
    }
}
