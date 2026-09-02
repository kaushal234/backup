<?php

declare(strict_types=1);

namespace App\Controller\Quality;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Dto\Quality\CrabSalesOrderLine;
use App\Entity\Quality\CrabFile;
use App\Entity\Sales\OrderLine;
use App\Factory\Quality\CrabSalesOrderLineFactory;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use App\Manager\Quality\CrabManager;
use App\Message\Quality\Crab\CrabWrite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrabFromOrderLineBatchController extends AbstractController
{
    public function __construct(
        private readonly CrabSalesOrderLineFactory $crabFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly IriConverterInterface $iriConverter,
        private readonly PersistableFileManagerFactory $persistableFileManagerFactory,
        private readonly CrabManager $crabManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(CrabSalesOrderLine $data, Request $request)
    {
        $violations = $this->validator->validate($data);

        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        $orderLine = $this->entityManager->getRepository(OrderLine::class)->findOneBy(['legacyId' => $data->orderLine]);

        if (null === $orderLine) {
            throw new BadRequestHttpException(\sprintf('SOL #%s not found', $data->orderLine));
        }

        $ordersToFactory = $orderLine->getFactoryOrders();
        $user = $this->iriConverter->getIriFromResource($this->getUser());

        foreach ($ordersToFactory as $orderToFactory) {
            $factory = $this->crabFactory;
            if (null === $orderToFactory->equipmentRecord || null !== $orderToFactory->equipmentRecord->getDateShipped()) {
                continue;
            }
            $crab = $factory($data, $orderToFactory->equipmentRecord);
            $this->entityManager->persist($crab);
            $this->entityManager->flush();
            $this->crabManager->updateIONCrabData($crab->equipmentRecord);

            if (null !== ($file = $request->files->get('file'))) {
                $this->persistableFileManagerFactory->getManagerForClass(CrabFile::class)->attach($crab, $file);
            }

            $this->messageBus->dispatch(new CrabWrite($user, $this->iriConverter->getIriFromResource($crab), Request::METHOD_POST));
        }
    }
}
