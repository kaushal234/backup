<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Finance\Currency;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:finance:currency')]
class ImportFinanceCurrencyCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy common currencies');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stmt = $this->legacyConnection->executeQuery("SELECT id, list_item FROM lists WHERE list_name = 'list.common.currency'");
        $this->helper->progressiveImport(
            $output, $stmt, Currency::class, 'legacyId', 'id',
            static function (Currency $type, array $data) {
                $type->setName($data['list_item']);
            }
        );

        return 0;
    }
}
