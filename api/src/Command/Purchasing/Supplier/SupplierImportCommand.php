<?php

declare(strict_types=1);

namespace App\Command\Purchasing\Supplier;

use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\Supplier\SupplierLocation;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelper;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:supplier:import', description: 'Import suppliers from CSV file, update if exists.')]
class SupplierImportCommand extends Command
{
    protected ?Supplier $supplier = null;

    protected EntityCacheHelper $locationCache;
    protected EntityCacheHelper $countryCache;
    protected EntityCacheHelper $currencyCache;
    protected PeopleRepository $peopleRepository;

    protected array $statusMap = [
        'tccom.prst.active' => Supplier::ACTIVE,
        'tccom.prst.inactive' => Supplier::INACTIVE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityCacheHelperFactory $cacheHelperFactory,
    ) {
        $this->peopleRepository = $this->entityManager->getRepository(People::class);

        $this->currencyCache = $cacheHelperFactory->createEntityCache(Currency::class, 'name');
        $this->countryCache = $cacheHelperFactory->createEntityCache(Country::class, 'isoCode2');
        $this->locationCache = $cacheHelperFactory->createEntityCache(Location::class, 'erp');

        parent::__construct();

        $this->addArgument('file', InputArgument::REQUIRED, 'Input file name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        // Count the number of suppliers in the file.
        $fileObject = new \SplFileObject($file, 'r');
        $fileObject->seek(\PHP_INT_MAX);
        $rows = $fileObject->key();

        if (($handle = fopen($file, 'r')) !== false) {
            $headers = fgetcsv(stream: $handle, separator: ';');
            $progressBar = new ProgressBar($output, $rows);
            while (($row = fgetcsv(stream: $handle, separator: ';')) !== false) {
                // Combine headers to keys of row data.
                $data = array_combine($headers, $row);

                // Get supplier and update.
                $this->supplier = $this->getSupplier($data['suppliercode']);
                $this->createOrUpdate($this->supplier, $data);
                $progressBar->advance();
            }
            $this->entityManager->clear();
            fclose($handle);
            $progressBar->finish();
        }

        return Command::SUCCESS;
    }

    /**
     * Find supplier or return new one empty.
     */
    protected function getSupplier(string $supplierCode): Supplier
    {
        // Get the last supplier used if the next one is the same.
        if ($this->supplier && $this->supplier->code === $supplierCode) {
            return $this->supplier;
        }

        $supplier = $this->entityManager->getRepository(Supplier::class)->findOneBy(['code' => $supplierCode]);
        if (!$supplier) {
            $supplier = new Supplier();
        }

        return $supplier;
    }

    /**
     * Depending on supplier code, create or update supplier.
     * Attach buy from the supplier location. Pair of location/buyer is unique.
     */
    protected function createOrUpdate(Supplier $supplier, array $data): void
    {
        $supplier->code = $data['suppliercode'];
        $supplier->name = $data['suppliername'];
        $supplier->location = !empty($data['masterbu']) ? $this->locationCache->fetch($data['masterbu']) : null;
        $supplier->country = !empty($data['country']) ? $this->countryCache->fetch($data['country']) : null;
        $supplier->currency = !empty($data['currency']) ? $this->currencyCache->fetch($data['currency']) : null;
        $supplier->status = $this->findStatus($supplier, $data['statusdescription']);

        $buyFrom = new SupplierLocation();
        $buyFrom->supplier = $supplier;
        $buyFrom->location = $this->locationCache->fetch($data['site']);
        $buyFrom->buyer = !empty($data['buyer']) ? $this->peopleRepository->find($data['buyer']) : null;
        $this->addBuyFrom($supplier, $buyFrom);

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();
    }

    /**
     * Map status and throw error if status is not found.
     */
    protected function findStatus(Supplier $supplier, string $statusCode): string
    {
        if (isset($this->statusMap[$statusCode])) {
            return $this->statusMap[$statusCode];
        }

        throw new \LogicException(\sprintf('Unknown status code: %s - %s', $statusCode, $supplier->code));
    }

    /**
     * Check the supplier location does not exist in the supplier object before adding it.
     */
    protected function addBuyFrom(Supplier $supplier, SupplierLocation $supplierLocation): void
    {
        foreach ($supplier->getBuyFrom() as $buyFrom) {
            if ($buyFrom->location->getErp() === $supplierLocation->location->getErp()) {
                return;
            }
        }

        $supplier->addBuyFrom($supplierLocation);
        $this->entityManager->persist($supplierLocation);
    }
}
