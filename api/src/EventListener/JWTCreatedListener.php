<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\UserConnectionRepository;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class JWTCreatedListener implements ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $serviceLocator)
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        if (null === $request = $this->serviceLocator->get(RequestStack::class)->getCurrentRequest()) {
            return;
        }

        if (null === $request->request->get(UserManager::LOGIN_PORTAL)) {
            return;
        }

        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($user instanceof People || $user instanceof ExtranetUser || $user instanceof VendorUser) {
            $this->serviceLocator->get(UserConnectionRepository::class)->addActivityLog($user, 'classic');
        }

        $payload = $event->getData();
        $this->serviceLocator->get(PayloadGeneratorInterface::class)->generate($payload);
        $event->setData($payload);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            PayloadGeneratorInterface::class,
            RequestStack::class,
            UserConnectionRepository::class,
        ];
    }
}
