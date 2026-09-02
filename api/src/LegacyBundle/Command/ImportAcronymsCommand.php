<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Acronym;
use App\Entity\AcronymCategory;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:acronyms')]
class ImportAcronymsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    /**
     * ImportAcronymsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Imports acronyms from legacy agr and agrl tables');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import Categories
        $sql = <<<'SQL'
            SELECT DISTINCT(type)
            FROM agrl
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $categories = $this->helper->progressiveImport(
            $output, $stmt, AcronymCategory::class, 'name', 'type',
            static function (AcronymCategory $category, array $data) {
            }
        );

        // Import acronyms
        $sql = <<<'SQL'
            SELECT agr.*, GROUP_CONCAT(agrl.type) AS acronym_categories
            FROM agr
            LEFT JOIN agrl ON agr.id = agrl.parent_id
            GROUP BY id
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Acronym::class, 'legacyId', 'id',
            static function (Acronym $acronym, array $data) use ($categories) {
                $acronym
                    ->setAcronym($data['acronym'])
                    ->setShortDescription($data['desca'])
                    ->setDescription($data['descb'] ?: null)
                    ->setUrl(mb_trim((string) $data['url']) ?: null)
                ;

                if ($data['acronym_categories']) {
                    foreach (explode(',', (string) $data['acronym_categories']) as $category) {
                        $acronym->addCategory($categories[$category]);
                    }
                }
            }
        );

        return 0;
    }
}
