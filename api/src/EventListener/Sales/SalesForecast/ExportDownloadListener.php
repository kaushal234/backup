<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ExportDownloadListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onSalesForecastExportResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        $format = $request->getRequestFormat();
        if (SalesForecast::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        if (!\in_array($format, ['csv', 'xlsx'], true)) {
            return;
        }

        // the frontend calls the API once per page, this is to prevent writing too many logs
        if ('1' !== $request->query->get('page', '1')) {
            return;
        }

        if ((null === $user = $this->serviceLocator->get(Security::class)->getUser()) || !$user instanceof People) {
            return;
        }

        $comment = (new Comment())
            ->setMessage(\sprintf('downloaded a <b>%s</b> export of SFR with the following parameters: %s',
                $format,
                preg_replace('/\s+/', ' ', json_encode($request->query->all(), \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT))
            ))
            ->setResource($this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($user))
            ->setCreatedAt(new \DateTime(Chronos::now()->toDateString()))
            ->setUser($user)
        ;

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($comment);
        $entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onSalesForecastExportResponse', EventPriorities::POST_RESPOND],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class, IriConverterInterface::class, EntityManagerInterface::class];
    }
}
