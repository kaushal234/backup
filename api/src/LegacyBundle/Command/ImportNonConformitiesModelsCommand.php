<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\NonConformity;
use App\Entity\Sales\Product;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:ncr_models')]
class ImportNonConformitiesModelsCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports NCR Models from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import NCR Models
        $sql = <<<'SQL'
                SELECT mod_models.id, mod_models.parent_id, mod_models.module, mod_models.model
                FROM mod_models
                LEFT JOIN ncr on mod_models.parent_id = ncr.id AND mod_models.module = 'NCR'
                WHERE ncr.t_emno != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $ncrCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');
        $productCache = $this->cacheFactory->createEntityCache(Product::class, 'name');

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($stmt->rowCount());

        $counter = 0;
        while ($data = $stmt->fetchAssociative()) {
            $progress->advance();
            $nonConformity = $ncrCache->fetch($data['parent_id']);
            if (!$nonConformity instanceof NonConformity || $nonConformity->environmentalIssue) {
                continue;
            }

            if ('OTHER' === $data['model'] || 'ENV' === $data['model']) {
                foreach ($nonConformity->getProducts() as $product) {
                    $nonConformity->removeProduct($product);
                }

                if ('ENV' === $data['model']) {
                    $nonConformity->environmentalIssue = true;
                }

                $this->entityManager->persist($nonConformity);

                if ($counter++ > 10000) {
                    $counter = 0;
                    $this->entityManager->flush();
                }

                continue;
            }

            /** @var Product|null $product */
            $product = $productCache->fetch($data['model']);
            if (null !== $product) {
                $nonConformity->addProduct($product);
                $this->entityManager->persist($nonConformity);

                if ($counter++ > 10000) {
                    $counter = 0;
                    $this->entityManager->flush();
                }
            }
        }

        $this->entityManager->flush();

        $progress->finish();

        return Command::SUCCESS;
    }
}
