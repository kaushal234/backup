<?php

declare(strict_types=1);

namespace App\DataProcessor;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\TaskInput;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Entity\Sales\Customer;
use App\Notifier\Tasks\LegacyTaskNotifier;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template T
 */
class TaskActionDataProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskManager $taskManager,
        private readonly Security $security,
        private readonly IriConverterInterface $iriConverter,
        private readonly ValidatorInterface $validator,
        private readonly LegacyTaskNotifier $notifier,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {
    }

    /**
     * @param TaskInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->validator->validate($data);

        /** @var People $user */
        $user = $this->security->getUser();
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $user->getBusinessUnit();
        /** @var Meeting $meeting */
        $meeting = $this->iriConverter->getResourceFromIri($data->getResource());

        $task = (new Task())
            ->setAssignee($data->getAssignee())
            ->setAssignor($user)
            ->setLocation($businessUnit->getLocation())
            ->setModule('MOM')
            ->setParentId($meeting->getId())
            ->setDescription($data->getDescription())
        ;

        if (null !== $data->getDueDate()) {
            $task->setDueDate($data->getDueDate());
        }
        if (null !== $data->getEscalationTrigger()) {
            $task->setEscalationTrigger($data->getEscalationTrigger());
        }

        foreach ($data->getCc() as $cc) {
            $task->addCc($cc);
        }

        try {
            $this->taskManager->insert($task);
        } catch (\Exception $exception) {
            throw new \LogicException('Task not created. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->notifier->sendEmail($task);

        $action = new Action();

        $action
            ->setAssignee($data->getAssignee())
            ->setDescription($data->getDescription())
            ->setMeeting($meeting)
            ->setTask($task->getId())
            ->setInitialDueDate($task->getDueDate())
            ->setInternal((bool) ($data->getMetadata()['internal'] ?? false))
        ;

        if (null !== ($customerIri = $data->getMetadata()['customer'] ?? null)) {
            $customer = $this->iriConverter->getResourceFromIri($customerIri);
            if ($customer instanceof Customer) {
                $action->setCustomer($customer);
            }
        }

        return $this->persistProcessor->process($action, $operation);
    }
}
