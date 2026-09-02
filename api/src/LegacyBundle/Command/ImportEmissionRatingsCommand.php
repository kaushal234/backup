<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\EmissionRating;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:emission_ratings')]
class ImportEmissionRatingsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import emission ratings from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import emission ratings
        $sql = <<<'SQL'
            SELECT id, list_item
            FROM lists
            WHERE list_name = 'list.engine.tiers';
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, EmissionRating::class, 'legacyId', 'id',
            static function (EmissionRating $emissionRating, array $data) {
                $emissionRating
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['list_item'])
                ;
            }, true
        );

        // Import obsolete emission ratings
        $sql = <<<'SQL'
            SELECT id, list_item
            FROM lists
            WHERE list_name = 'list.engine.tiers_obsolete';
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, EmissionRating::class, 'legacyId', 'id',
            static function (EmissionRating $emissionRating, array $data) {
                $emissionRating
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['list_item'])
                    ->setObsolete(true)
                ;
            }, true
        );

        return 0;
    }
}
