<?php

declare(strict_types=1);

namespace App\Command\Quality\Crab;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Quality\Crab;
use App\ION\DataProcessor\IONDataProcessor;
use App\ION\Resources\Manufacturing\ProductionOrder\ProductionOrder;
use App\ION\Resources\Manufacturing\ProductionOrder\ProductionOrderProject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ln:crab:export')]
class ExportCrabInformationToLnCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IONDataProcessor $dataPersister,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Sort data.');
        $crabRepository = $this->em->getRepository(Crab::class);

        $crabs = $crabRepository->findAll();
        $pg = new ProgressBar($output, \count($crabs));
        $exports = [];
        /** @var Crab $crab */
        foreach ($crabs as $crab) {
            $pg->advance();
            if (null === ($equipmentRecord = $crab->equipmentRecord) || null === ($factoryErp = $equipmentRecord->getManufacturerLocation()?->getErp())) {
                continue;
            }

            $serialNumber = $equipmentRecord->getSerialNumber();
            if (\array_key_exists($factoryErp, $exports)
                && \array_key_exists($serialNumber, $exports[$factoryErp])
            ) {
                ++$exports[$factoryErp][$serialNumber]['totalCrabs'];
                $exports[$factoryErp][$serialNumber]['openCrabs'] += (int) (Crab::CLOSED !== $crab->status);
            } else {
                $exports[$factoryErp][$serialNumber] = [
                    'totalCrabs' => 1,
                    'openCrabs' => (int) (Crab::CLOSED !== $crab->status),
                ];
            }
        }
        $pg->finish();

        foreach ($exports as $key => $export) {
            $productionOrder = new ProductionOrder();
            $productionOrder->site = $key;
            foreach ($export as $serialNumber => $data) {
                $project = new ProductionOrderProject();
                $project->code = (string) $serialNumber;
                $project->openCrabs = $data['openCrabs'];
                $project->totalCrabs = $data['totalCrabs'];
                $productionOrder->addProject($project);
            }

            $operation = $this->resourceMetadataCollectionFactory->create(ProductionOrder::class)->getOperation('post_production_order');
            $this->dataPersister->process($productionOrder, $operation, ['resource_class' => ProductionOrder::class]);
        }

        return Command::SUCCESS;
    }
}
