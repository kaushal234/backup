<?php

declare(strict_types=1);

namespace App\Command\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Finance\ManufacturingMargin;
use App\Entity\Quality\Crab;
use App\Entity\Sales\Demo;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Entity\Sales\OrderToFactory;
use App\Entity\Sales\OrderTransaction;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\HourMeterTransaction;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use App\Entity\Support\ManualPart;
use App\Entity\Support\ManualPrint;
use App\FileSystem\Storage\FileStorageHandlerFactory;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\EquipmentRecordManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'api:equipment:cleanup')]
class EquipmentRecordCleanupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EquipmentRecordManager $equipmentRecordManager,
        private readonly ParameterBagInterface $parameters,
        private readonly FileStorageHandlerFactory $storageHandlerFactory
    ) {
        parent::__construct();
        $this->setDescription('Cleanup of ER that does not exists anymore in legacy database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $equipmentRecordRepository = $this->entityManager->getRepository(EquipmentRecord::class);
        $crabRepository = $this->entityManager->getRepository(Crab::class);
        $equipmentShippingRecordLineRepository = $this->entityManager->getRepository(EquipmentShippingRecordLine::class);
        $manualRepository = $this->entityManager->getRepository(Manual::class);
        $manualPartRepository = $this->entityManager->getRepository(ManualPart::class);
        $manualDocumentRepository = $this->entityManager->getRepository(ManualDocument::class);
        $manualPrintRepository = $this->entityManager->getRepository(ManualPrint::class);
        $equipmentSerialRepository = $this->entityManager->getRepository(EquipmentSerial::class);
        $manufacturingMarginRepository = $this->entityManager->getRepository(ManufacturingMargin::class);
        $demoRepository = $this->entityManager->getRepository(Demo::class);
        $orderToFactoryRepository = $this->entityManager->getRepository(OrderToFactory::class);
        $hourmeterRepository = $this->entityManager->getRepository(HourMeterTransaction::class);
        $salesOrderTransactionRepository = $this->entityManager->getRepository(OrderTransaction::class);
        $estimatedGreenTagQuantityReportRepository = $this->entityManager->getRepository(EstimatedGreenTagQuantityReport::class);
        $customerServiceRecordRepository = $this->entityManager->getRepository(AbstractCustomerServiceRecord::class);
        $interventionRepository = $this->entityManager->getRepository(Intervention::class);

        /** @var EquipmentRecord $equipmentRecord */
        foreach ($equipmentRecordRepository->findAll() as $equipmentRecord) {
            /** @var array $legacyEquipmentRecord */
            $legacyEquipmentRecord = $this->equipmentRecordManager->findByLegacyId($equipmentRecord->getLegacyId());
            if (empty($legacyEquipmentRecord)) {
                foreach ($estimatedGreenTagQuantityReportRepository->findByEquipmentRecord($equipmentRecord) as $report) {
                    $report->removeEquipmentRecord($equipmentRecord);
                    if (0 === \count($report->getEquipmentRecords())) {
                        $this->entityManager->remove($report);
                    }
                    $output->writeln(\sprintf('ER %s removed from EstimatedGreenTagQuantityReport #%d', $equipmentRecord->getId(), $report->getId()));
                }
                foreach ($customerServiceRecordRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $customerServiceRecord) {
                    foreach ($interventionRepository->findBy(['customerServiceRecord' => $customerServiceRecord]) as $intervention) {
                        $output->writeln(\sprintf('Intervention %d removed from API', $intervention->getId()));
                        // Intervention is Gedmo SoftDeleteable: remove() only soft-deletes unless deletedAt
                        // is already set in the past, so force it here to get a real hard delete.
                        $intervention->deletedAt = new \DateTime('1970-01-01');
                        $this->entityManager->remove($intervention);
                    }

                    $output->writeln(\sprintf('Hour Meter %s removed from API', $customerServiceRecord->getId()));
                    // Same as above: CustomerServiceRecord is Gedmo SoftDeleteable.
                    $customerServiceRecord->deletedAt = new \DateTime('1970-01-01');
                    $this->entityManager->remove($customerServiceRecord);
                }
                foreach ($hourmeterRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $hourMeter) {
                    $output->writeln(\sprintf('Hour Meter %s removed from API', $hourMeter->getId()));
                    $this->entityManager->remove($hourMeter);
                }
                foreach ($orderToFactoryRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $orderToFactory) {
                    $output->writeln(\sprintf('OrderToFactory %s removed from API', $orderToFactory->getId()));
                    $this->entityManager->remove($orderToFactory);
                }
                foreach ($salesOrderTransactionRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $orderTransaction) {
                    $output->writeln(\sprintf('OrderTransaction %s removed from API', $orderTransaction->getId()));
                    $this->entityManager->remove($orderTransaction);
                }
                foreach ($crabRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $crab) {
                    $output->writeln(\sprintf('CRAB %s removed from API', $crab->getId()));
                    $this->entityManager->remove($crab);
                }
                foreach ($equipmentRecordRepository->findBy(['parentEquipment' => $equipmentRecord]) as $childEquipmentRecord) {
                    $output->writeln(\sprintf('ER parent removed from ER %s', $childEquipmentRecord->getId()));
                    $childEquipmentRecord->setParentEquipment(null);
                    $this->entityManager->persist($childEquipmentRecord);
                }
                foreach ($manufacturingMarginRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $manufacturingMargin) {
                    $output->writeln(\sprintf('Manufacturing Margin %s removed from API', $manufacturingMargin->getId()));
                    $this->entityManager->remove($manufacturingMargin);
                }
                foreach ($demoRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $demo) {
                    $output->writeln(\sprintf('ER removed from DEMO %s', $demo->getId()));
                    $demo->setEquipmentRecord(null);
                    $this->entityManager->persist($demo);
                }
                foreach ($equipmentShippingRecordLineRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $equipmentShippingRecordLine) {
                    $output->writeln(\sprintf('ESRL %s removed from API', $equipmentShippingRecordLine->getId()));
                    $this->entityManager->remove($equipmentShippingRecordLine);
                }
                foreach ($manualRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $manual) {
                    foreach ($manualPrintRepository->findBy(['manual' => $manual]) as $manualPrint) {
                        $output->writeln(\sprintf('Manual Print %s removed from API', $manualPrint->getId()));
                        $this->entityManager->remove($manualPrint);
                    }
                    foreach ($manualDocumentRepository->findBy(['manual' => $manual]) as $manualDocument) {
                        foreach ($manualPartRepository->findBy(['document' => $manualDocument]) as $manualPart) {
                            $output->writeln(\sprintf('Manual Part %s removed from API', $manualPart->getId()));
                            $this->entityManager->remove($manualPart);
                        }

                        if (null !== ($file = $manualDocument->getDocument())) {
                            try {
                                $fileToDelete = new File($this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath());
                            } catch (FileNotFoundException $e) {
                                $output->writeln('File not found');
                                $fileToDelete = null;
                            }

                            if (null !== $fileToDelete) {
                                $storageHandler = $this->storageHandlerFactory->getStorageHandlerForClass(ManualDocumentFile::class);
                                $filename = $fileToDelete->getPath().'/'.$fileToDelete->getFilename();
                                $storageHandler->remove($filename);
                                $output->writeln(\sprintf('File %s removed from storage', $filename));
                            }

                            $this->entityManager->remove($file);
                        }

                        $output->writeln(\sprintf('Manual Document %s removed from API', $manualDocument->getId()));
                        $this->entityManager->remove($manualDocument);
                    }
                    $output->writeln(\sprintf('Manual %s removed from API', $manual->getId()));
                    $this->entityManager->remove($manual);
                }

                foreach ($equipmentSerialRepository->findBy(['equipmentRecord' => $equipmentRecord]) as $equipmentSerial) {
                    $output->writeln(\sprintf('Equipment Serial %s removed from API', $equipmentSerial->getId()));
                    $this->entityManager->remove($equipmentSerial);
                }

                $output->writeln(\sprintf('ER %s removed from API', $equipmentRecord->getSerialNumber()));
                $this->entityManager->remove($equipmentRecord);
                $this->entityManager->flush();
            }
        }

        return Command::SUCCESS;
    }
}
