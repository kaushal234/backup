<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Task\Task;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallSurveyNotifier;
use App\Notifier\Tasks\TaskNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'removeToken', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onPost', priority: EventPriorities::POST_WRITE)]
readonly class TechnicianOnCallSurveyListener implements ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function removeToken(ViewEvent $event): void
    {
        $survey = $event->getControllerResult();

        if (!$survey instanceof TechnicianOnCallSurvey) {
            return;
        }

        if ('POST' !== $event->getRequest()->getMethod()) {
            return;
        }

        $survey->technicianOnCall->token = null;
    }

    public function onPost(ViewEvent $event): void
    {
        $survey = $event->getControllerResult();

        if (!$survey instanceof TechnicianOnCallSurvey) {
            return;
        }

        if ('POST' !== $event->getRequest()->getMethod()) {
            return;
        }

        if ($survey->isPerfect()) {
            return;
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $peopleRepository = $entityManager->getRepository(People::class);

        $ccs = new ArrayCollection();
        $evps = $peopleRepository->findGroupsMembers(['ROLE_EVP'], $survey->technicianOnCall->salesOrganisationService);
        $csds = $peopleRepository->findGroupsMembers(['ROLE_CSD']);

        try {
            /** @var People $csd */
            $csd = $csds[0];
            foreach ($csds as $csd) {
                $ccs->add($csd);
            }

            if (empty($evps)) {
                throw new \RuntimeException($translator->trans('toc.messages.errors.no_evp', [], 'technician_on_call'));
            }

            /** @var People $evp */
            $evp = $evps[0];
            foreach ($evps as $people) {
                $ccs->add($people);
            }

            // Add CC
            $locationRepository = $entityManager->getRepository(Location::class);
            $gcoos = $peopleRepository->findGroupsMembers(['ROLE_GCOO']);
            $coos = $peopleRepository->findGroupsMembers(['ROLE_CEO'], $survey->technicianOnCall->salesOrganisationService);
            $csds = $peopleRepository->findGroupsMembers(['ROLE_CSD'], $locationRepository->findOneBy(['erp' => 900]));
            $gseMembers = $peopleRepository->findGroupsMembers(['ROLE_TCOO', 'ROLE_CSM'], $locationRepository->findOneBy(['erp' => 997]));

            foreach ($gcoos as $gcoo) {
                $ccs->add($gcoo);
            }
            foreach ($coos as $coo) {
                $ccs->add($coo);
            }

            foreach ($csds as $csd) {
                $ccs->add($csd);
            }

            foreach ($gseMembers as $gseMember) {
                $ccs->add($gseMember);
            }

            $dueDate = new \DateTime('+3 days');
            $tocModule = $entityManager->getRepository(Module::class)->findOneBy(['name' => TechnicianOnCall::MODULE_NAME]);

            $task = new Task();
            $task->assignee = $evp;
            $task->createdBy = $csd;
            $task->module = $tocModule;
            $task->referenceId = $survey->technicianOnCall->getId();
            $task->shortDescription = 'TOC Survey Below 5/5/5/5';
            $task->description = 'Please call the customer within 3 days to acknowledge the customer feedback and learn what it takes to meet 5/5/5/5 expectations going forward.';
            $task->dueDate = $dueDate;
            $task->startedAt = new \DateTime();
            $task->location = $evp->getBusinessUnit()->getLocation();
            foreach ($ccs as $cc) {
                $task->addRecipient($cc);
            }

            $entityManager->persist($task);
            $entityManager->flush();

            /** @var TaskNotifier $taskNotifier */
            $taskNotifier = $this->container->get(TaskNotifier::class);
            $taskNotifier->sendBadSurveyEmail($task, $survey);
        } catch (\Exception $exception) {
            if ($exception instanceof \RuntimeException) {
                $message = $exception->getMessage();
            } else {
                $message = 'An error occurred while trying to create task and send the email.';
            }

            /** @var TechnicianOnCallSurveyNotifier $technicianOnCallSurveyNotifier */
            $technicianOnCallSurveyNotifier = $this->container->get(TechnicianOnCallSurveyNotifier::class);
            $technicianOnCallSurveyNotifier->sendErrorTaskCreationEmail($survey, $csd, [$message]);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            TranslatorInterface::class,
            TaskNotifier::class,
            TechnicianOnCallSurveyNotifier::class,
        ];
    }
}
