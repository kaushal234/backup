<?php

declare(strict_types=1);

namespace App\Factory\Task;

use ApiPlatform\Metadata\Exception\AccessDeniedException;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\Task\TaskBatchCreation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Task\Task;
use App\Entity\Task\Template;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TaskCheckContactCampaignFactory implements TaskBatchCreationFactoryInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private ?Template $template = null,
    ) {
    }

    public function supports(string $type): bool
    {
        return 'contact.validation.address' === $type;
    }

    public function create(TaskBatchCreation $data, int $referenceId): Task
    {
        $this->validator->validate($data, ['groups' => ['contact_campaign']]);

        if (!$this->security->isGranted('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER')) {
            throw new AccessDeniedException('You do not have permission to create tasks for contact campaign');
        }

        $userRepository = $this->entityManager->getRepository(ExtranetUser::class);
        $contact = $userRepository->findOneBy(['id' => $referenceId]);
        $asm = $contact->getExtranetUSerProfile()->customer?->getMainSalesRepresentative()->asm ?? null;
        $description = \sprintf('Please update the econtact %d (email, address etc..) for the campaign %d, and close the task to confirm it’s up to date.', $contact->getId(), $data->campaign->getId());

        $task = new Task();
        $task->shortDescription = 'Contact Address Validation';
        $task->module = $data->module;
        $task->referenceId = $referenceId;
        $task->dueDate = (new \DateTime())->modify('+ 15 days');
        $task->template = $this->template;
        $task->assignee = $asm ?? $data->campaign->owner;
        $task->createdBy = $asm ?? $data->campaign->owner;
        $task->startedAt = (new \DateTimeImmutable('now'));
        $task->description = $asm ? $description : $description.'This task is assigned to you because the customer has no ASM';

        return $task;
    }

    public function shouldCreateTask(int $referenceId): bool
    {
        $templateRepository = $this->entityManager->getRepository(Template::class);
        $this->template = $template = $templateRepository->findOneBy(['name' => 'contact.validation.address']);

        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['referenceId' => $referenceId, 'template' => $template]);
        $task = !empty($tasks) ? end($tasks) : null;

        return null === $task || 'CLOSED' === $task->getStatus();
    }
}
