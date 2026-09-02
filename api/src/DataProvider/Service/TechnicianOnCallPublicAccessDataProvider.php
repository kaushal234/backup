<?php

declare(strict_types=1);

namespace App\DataProvider\Service;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TechnicianOnCallPublicAccessDataProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')] private ProviderInterface $provider,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $token = $request->query->get('token');

        if (null === $token || '' === $token) {
            throw new BadRequestHttpException('Query parameter "token" is required.');
        }

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->provider->provide($operation, $uriVariables, $context);

        if (null === $technicianOnCall || $token !== $technicianOnCall->token) {
            throw new NotFoundHttpException('TOC not found.');
        }

        return $technicianOnCall;
    }
}
