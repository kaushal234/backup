<?php

declare(strict_types=1);

namespace App\Jira\EventListener;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\Module\Specification\UserStory;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\Issue;
use App\Jira\Resources\TroubleTicketIssue;
use App\Jira\Resources\UserStoryIssue;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class IssueListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['postCreate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function postCreate(ViewEvent $event)
    {
        $data = $event->getControllerResult();
        if ((!$data instanceof TroubleTicketIssue && !$data instanceof UserStoryIssue) || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $itemDataProvider = $this->serviceLocator->get('jira.item_data_provider');
        /** @var ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory */
        $resourceMetadataFactory = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class);
        $metadata = $resourceMetadataFactory->create($data::class);
        /** @var Issue $issue */
        $issue = $itemDataProvider->provide($metadata->getOperation(), ['id' => $data->id]);

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        switch (true) {
            case $issue instanceof TroubleTicketIssue:
                /** @var TroubleTicket $object */
                $object = $entityManager->getRepository(TroubleTicket::class)->findOneBy(['id' => $issue->troubleTicketId]);
                break;
            case $issue instanceof UserStoryIssue:
                /** @var UserStory $object */
                $object = $entityManager->getRepository(UserStory::class)->findOneBy(['id' => $issue->userStoryId]);
                break;
            default:
                return;
        }

        $object->jiraIssueNumber = $issue->id;

        $entityManager->persist($object);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            'jira.item_data_provider' => JiraItemDataProvider::class,
            ResourceMetadataCollectionFactoryInterface::class,
        ];
    }
}
