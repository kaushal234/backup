<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:product_types')]
class ImportSalesCatalogueProductTypeCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy catalogue product types');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->sanitationHelper = $sanitationHelper;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import catalog product types
        $sql = <<<'SQL'
            SELECT id, en, fr, es, pt, zh, ja, de, ru
            FROM products_categories
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, ProductType::class, 'legacyId', 'id',
            function (ProductType $type, array $data) {
                $type
                    ->setLegacyId((int) $data['id'])
                    ->setEnglishName(mb_trim($this->sanitationHelper->parse($data['en'])))
                    ->setFrenchName(mb_trim($this->sanitationHelper->parse($data['fr'])))
                    ->setSpanishName(mb_trim($this->sanitationHelper->parse($data['es'])))
                    ->setPortugueseName(mb_trim($this->sanitationHelper->parse($data['pt'])))
                    ->setChineseName(mb_trim($this->sanitationHelper->parse($data['zh'])))
                    ->setJapaneseName(mb_trim($this->sanitationHelper->parse($data['ja'])))
                    ->setGermanName(mb_trim($this->sanitationHelper->parse($data['de'])))
                    ->setRussianName(mb_trim($this->sanitationHelper->parse($data['ru'])))
                ;
            }, true
        );

        return 0;
    }
}
