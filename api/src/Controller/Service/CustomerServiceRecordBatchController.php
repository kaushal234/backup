<?php

declare(strict_types=1);

namespace App\Controller\Service;

use App\Dto\Service\CustomerServiceRecordBatch;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CustomerServiceRecordBatchController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        public readonly WorkflowStatusUpdater $workflowStatusUpdater,
    ) {
    }

    public function __invoke(CustomerServiceRecordBatch $data): Response
    {
        foreach ($data->getCustomerServiceRecords() as $customerServiceRecord) {
            try {
                $this->workflowStatusUpdater->applyStatus($customerServiceRecord, AbstractCustomerServiceRecord::CLOSED);
            } catch (\LogicException $exception) {
                throw new BadRequestHttpException($exception->getMessage(), $exception);
            }
            $this->entityManager->persist($customerServiceRecord);
        }
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
