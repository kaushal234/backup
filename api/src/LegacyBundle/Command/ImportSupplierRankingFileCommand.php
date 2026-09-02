<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Purchasing\SupplierRanking\FileCategory;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:supplier_rankings:files')]
class ImportSupplierRankingFileCommand extends Command
{
    private $categoriesMapping = [
        'Qualification' => [0, 1, 101, 1101],
        'Contracts' => [2, 102, 1002, 1102],
        'Prices' => [3, 103, 1003, 1103],
        'Minutes of meeting' => [4, 104, 1004, 1104],
        'Code Ethic' => [5, 105, 1005, 1105],
        'Others' => [6, 106, 1006, 1106],
        'ISO9001' => [7, 107, 1007, 1107],
        'ISO14001' => [8, 108, 1008, 1108],
        'ESG' => [9, 109, 1009, 1109],
    ];

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly FileHelper $fileHelper,
        private readonly EntityCacheHelperFactory $cacheFactory,
    ) {
        parent::__construct();
        $this->setDescription('Import supplier ranking files from legacy');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'email');
        $categoryCache = $this->cacheFactory->createEntityCache(FileCategory::class, 'name');

        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select("mf.parent_id, module, f.poster, dt as created_at, description, REPLACE(REPLACE(REPLACE(REPLACE(f.filepath, '/var/www/intranet/current/uploads/mod_files/', ''), '/var/www/intranet/current/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/uploads/mod_files/', '') as filename, f.filename AS originalFilename, f.extension, f.mime, mf.level, mf.expiration_date")
            ->from('mod_files', 'mf')
            ->join('mf', 'file', 'f', 'mf.fid = f.id')
            ->where('module = :module')
            ->setParameter('module', 'eVendor-Q')
        ;
        $result = $queryBuilder->executeQuery();

        $this->fileHelper->import(
            $result,
            $output,
            SupplierRanking::class,
            SupplierRankingFile::class,
            'filename',
            'parent_id',
            'mod_files',
            function ($data) use ($peopleCache, $categoryCache) {
                return $this->onPreImport($data, $peopleCache, $categoryCache, $this->categoriesMapping);
            }
        );

        return 0;
    }

    protected function onPreImport($data, $peopleCache, $categoryCache, $categoriesMapping): array
    {
        $metadata = [];
        if ($data['description']) {
            $metadata['description'] = $data['description'];
        }

        if (null !== $poster = $peopleCache->fetch($data['poster'])) {
            $metadata['poster'] = $poster;
        }

        if ($data['created_at']) {
            $metadata['created_at'] = new \DateTime($data['created_at']);
        }

        if ($data['expiration_date']) {
            $metadata['expiredAt'] = new \DateTime($data['expiration_date']);
        }

        $metadata['category'] = $categoryCache->fetch('Others');
        if ($data['level']) {
            foreach ($categoriesMapping as $category => $mapping) {
                if (\in_array($data['level'], $mapping, true)) {
                    $metadata['category'] = $categoryCache->fetch($category);
                }
            }
        }

        return $metadata;
    }
}
