<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\EquipmentRecord;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportBatchHelper;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:serials')]
class ImportEquipmentSerialsCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly ImportHelper $helper;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheHelperFactory,
        SanitationHelper $sanitationHelper
    ) {
        parent::__construct();
        $this->setDescription('Import equipment serials from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    protected function configure(): void
    {
        ImportBatchHelper::addArguments($this);
        $this->addArgument('startId', InputArgument::OPTIONAL, 'Starting id', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT
                se.id,
                se.parent_id,
                se.component,
                se.model,
                se.serial,
                se.brand
            FROM service_serials se
            LEFT JOIN service er ON se.parent_id = er.id
            WHERE er.sn != 'PLEASE CHANGE'
            SQL;

        $startId = $input->getArgument('startId');
        if ($startId > 0) {
            $sql .= 'AND se.id >= :startId';
            $stmt = $this->legacyConnection->executeQuery(
                $sql,
                ['startId' => $startId],
                ['startId' => ParameterType::INTEGER]
            );
        } else {
            $sql .= 'LIMIT :limit OFFSET :offset';
            $stmt = $this->legacyConnection->executeQuery(
                $sql,
                [
                    'offset' => $input->getArgument('offset'),
                    'limit' => $input->getArgument('limit'),
                ],
                [
                    'offset' => ParameterType::INTEGER,
                    'limit' => ParameterType::INTEGER,
                ]
            );
        }

        $componentCache = $this->cacheHelperFactory->createEntityCache(Component::class, 'name');
        $equipmentCache = $this->cacheHelperFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $sanitationHelper = $this->sanitationHelper;

        $this->helper->disableValidation();
        $this->helper->setBatchSize(10_000);
        $this->helper->progressiveImport(
            $output, $stmt, EquipmentSerial::class, 'legacyId', 'id',
            static function (EquipmentSerial $serial, array $data) use ($componentCache, $equipmentCache, $sanitationHelper) {
                $equipment = $equipmentCache->fetch($data['parent_id']);
                if (null === $equipment) {
                    throw new \InvalidArgumentException('equipment not found');
                }
                $serial->equipmentRecord = $equipment;
                $serial->component = $componentCache->fetch($data['component']);
                $serial->model = $data['model'] ? $sanitationHelper->parse($data['model']) : null;
                $serial->serial = $data['serial'] ? $sanitationHelper->parse($data['serial']) : null;
                $serial->brand = $data['brand'] ? $sanitationHelper->parse($data['brand']) : null;
                $serial->setLegacyId((int) $data['id']);
            },
            true,
        );

        return Command::SUCCESS;
    }
}
