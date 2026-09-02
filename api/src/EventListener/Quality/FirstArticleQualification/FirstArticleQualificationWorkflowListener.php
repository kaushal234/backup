<?php

declare(strict_types=1);

namespace App\EventListener\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Notifier\Tasks\LegacyTaskNotifier;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FirstArticleQualificationWorkflowListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onCompleted(Event $event)
    {
        /** @var FirstArticleQualification $firstArticleQualification */
        $firstArticleQualification = $event->getSubject();
        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)->sendStatus($firstArticleQualification);

        if (FirstArticleQualification::QUALIFIED === $firstArticleQualification->getStatus() && null !== $firstArticleQualification->getBuyer()) {
            /** @var TaskManager $taskManager */
            $taskManager = $this->serviceLocator->get(TaskManager::class);
            $task = (new Task())
                ->setModule('FAQ')
                ->setLocation($firstArticleQualification->getLocation())
                ->setParentId($firstArticleQualification->getId())
                ->setAssignee($firstArticleQualification->getBuyer())
                ->setAssignor($firstArticleQualification->getBuyer())
                ->setEscalationTrigger(10)
                ->setDueDate(new \DateTime('+10 days'))
                ->setDescription(\sprintf('FAQ%d is QUALIFIED, please update setup of PN in LN.', $firstArticleQualification->getId()))
            ;

            $taskManager->insert($task);

            $this->serviceLocator->get(LegacyTaskNotifier::class)->sendEmail($task);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.first_article_qualification.completed.to_in_progress' => ['onCompleted'],
            'workflow.first_article_qualification.completed.to_rejected' => ['onCompleted'],
            'workflow.first_article_qualification.completed.to_conditional' => ['onCompleted'],
            'workflow.first_article_qualification.completed.to_qualified' => ['onCompleted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            FirstArticleQualificationNotifier::class,
            TaskManager::class,
            LegacyTaskNotifier::class,
        ];
    }
}
