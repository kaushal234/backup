<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Common\Notification\Notification;
use App\Entity\Directory\People;
use App\Manager\Transfer\EntityTransferManager;
use App\Manager\Transfer\Model\TransferPeopleModel;
use App\Repository\Directory\PeopleRepository;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class PeopleTransferController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityTransferManager $genericManager,
        private readonly EntityTransferManager $customerRepresentativeManager,
        private readonly EntityTransferManager $serviceRepresentativeManager,
        private readonly EntityTransferManager $partsRepresentativeManager,
        private readonly EntityTransferManager $salesRepresentativeManager,
        private readonly EntityTransferManager $supervisorRepresentativeManager,
        private readonly PeopleRepository $peopleRepository,
        private readonly CommentRequestManager $commentRequestManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(People $people, Request $request)
    {
        /** @var TransferPeopleModel $transferParams */
        $transferParams = $this->serializer->deserialize($request->getContent(), TransferPeopleModel::class, JsonEncoder::FORMAT);

        $comments = [];

        if (null !== $transferParams->getTarget()) {
            $this->genericManager->transfer($people, $transferParams->getTarget());
            $comments[] = \sprintf('Generic intranet resources transferred to %s', $transferParams->getTarget());
        }

        if (null !== $asmTarget = $transferParams->getAsmTarget()) {
            $this->customerRepresentativeManager->transfer($people, $asmTarget);
            $comments[] = \sprintf('ASM intranet resources transferred to %s', $transferParams->getAsmTarget());
        }

        if (null !== $supervisorTarget = $transferParams->getSupervisorTarget()) {
            $this->supervisorRepresentativeManager->transfer($people, $supervisorTarget);
            $comments[] = \sprintf('Supervisor updated to %s', $transferParams->getSupervisorTarget());
        }

        if (null !== $serviceRepTarget = $transferParams->getServiceRepTarget()) {
            $this->serviceRepresentativeManager->transfer($people, $serviceRepTarget);
            $comments[] = \sprintf('Service intranet resources transferred to %s', $transferParams->getServiceRepTarget());
        }

        if (null !== $partsRepTarget = $transferParams->getPartsRepTarget()) {
            $this->partsRepresentativeManager->transfer($people, $partsRepTarget);
            $comments[] = \sprintf('Parts intranet resources transferred to %s', $transferParams->getPartsRepTarget());
        }

        if (null !== $salesRepTarget = $transferParams->getSalesRepTarget()) {
            $this->salesRepresentativeManager->transfer($people, $salesRepTarget);
            $comments[] = \sprintf('Sales intranet resources transferred to %s', $transferParams->getSalesRepTarget());
        }

        if (null !== $transferParams->getTarget()) {
            $this->peopleRepository->disabledPeople($people);
        }

        foreach ($this->entityManager->getRepository(Notification::class)->findBy(['people' => $people]) as $notification) {
            $this->entityManager->remove($notification);
        }

        foreach ($comments as $comment) {
            $this->commentRequestManager->insertComment($people, $comment);
        }

        return $people;
    }
}
