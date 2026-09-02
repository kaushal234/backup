<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\FreightForwarder;
use App\Entity\Sales\Customer;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\Incoterm;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:data')]
class ImportEquipmentShippingRecordCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, SanitationHelper $sanitationHelper, EntityCacheHelperFactory $cacheHelperFactory, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports equipment shipping record from legacy esr table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $locationCache = $this->cacheHelperFactory->createEntityCache(Location::class, 'legacyId');
        $customerCache = $this->cacheHelperFactory->createEntityCache(Customer::class, 'legacyId');
        $incotermCache = $this->cacheHelperFactory->createEntityCache(Incoterm::class, 'code');

        $freightForwarders = $this->entityManager->getRepository(FreightForwarder::class)->findAll();
        $conveyors = [];
        /** @var FreightForwarder $forwarder */
        foreach ($freightForwarders as $forwarder) {
            foreach ($forwarder->getEmails() as $email) {
                $conveyors[mb_trim($email)] = $forwarder;
            }
        }

        $logger->info('Importing esr');
        $sql = <<<'SQL'
            SELECT esr.id, sso_id, cuid, dt_open, status, inco, load_place, departure, arrival, modality, notes, ship_auth,
                   carrier.email as carrierEmail,
                   carrier.company as carrierCompany,
                   forwarder.email as forwarderEmail,
                   forwarder.company as forwarderCompany
            FROM esr
            LEFT JOIN vendors AS carrier ON esr.car_id = carrier.id
            LEFT JOIN vendors AS forwarder ON esr.fwd_id = forwarder.id
            WHERE cuid IN (SELECT id FROM customers)
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);
        $this->helper->progressiveImport(
            $output, $stmt, EquipmentShippingRecord::class, 'legacyId', 'id',
            function (EquipmentShippingRecord $equipmentShippingRecord, array $data) use ($locationCache, $customerCache, $incotermCache, $conveyors) {
                $equipmentShippingRecord->sso = $locationCache->fetch((string) $data['sso_id']);
                $equipmentShippingRecord->customer = $customerCache->fetch((string) $data['cuid']);
                $equipmentShippingRecord->incoterm = $incotermCache->fetch((string) $data['inco']);
                $equipmentShippingRecord->setStatus($data['status']);
                $equipmentShippingRecord->createdAt = '0000-00-00' === $data['dt_open'] ? null : new \DateTime($data['dt_open']);
                $equipmentShippingRecord->loadingPlace = '' === $data['load_place'] ? null : $data['load_place'];
                $equipmentShippingRecord->departurePlace = '' === $data['departure'] ? null : $data['departure'];
                $equipmentShippingRecord->arrivalPlace = '' === $data['arrival'] ? null : $data['arrival'];
                $equipmentShippingRecord->modality = $data['modality'];
                $equipmentShippingRecord->notes = '' === $data['notes'] ? null : mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['notes'])));
                $equipmentShippingRecord->shipAuthorization = (bool) $data['ship_auth'];

                if (null === $data['carrierEmail']) {
                    $equipmentShippingRecord->carrier = null;
                } elseif (!\array_key_exists($data['carrierEmail'], $conveyors)) {
                    $equipmentShippingRecord->notes .= \sprintf('\n Carrier: %s %s', $data['carrierEmail'], $data['carrierCompany']);
                } else {
                    $equipmentShippingRecord->carrier = $conveyors[$data['carrierEmail']];
                }

                if (null === $data['forwarderEmail']) {
                    $equipmentShippingRecord->forwarder = null;
                } elseif (!\array_key_exists($data['forwarderEmail'], $conveyors)) {
                    $equipmentShippingRecord->notes .= \sprintf('\n Forwarder : %s %s', $data['forwarderEmail'], $data['forwarderCompany']);
                } else {
                    $equipmentShippingRecord->forwarder = $conveyors[$data['forwarderEmail']];
                }
            }
        );

        return Command::SUCCESS;
    }
}
