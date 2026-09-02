<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:finance:exchange_rates')]
class ImportFinanceExchangeRateCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheHelper)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy exchange rates');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelper = $cacheHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, dt, nam_year, nam_month, nam_cur, typ, rate FROM erp_forex2
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $currencyCache = $this->cacheHelper->createEntityCache(Currency::class, 'name');

        $this->helper->progressiveImport(
            $output, $stmt, ExchangeRate::class, 'legacyId', 'id',
            static function (ExchangeRate $type, array $data) use ($currencyCache) {
                /** @var \DateTime $applicatedOn */
                $applicatedOn = \DateTime::createFromFormat('Y-n-d H:i:s', $data['nam_year'].'-'.$data['nam_month'].'-01 00:00:00');
                $type
                    ->setCreatedAt('0000-00-00 00:00:00' !== $data['dt'] ? new \DateTime($data['dt']) : $applicatedOn)
                    ->setApplicatedOn($applicatedOn)
                    ->setRate($data['rate'])
                    ->setCurrency($currencyCache->fetch($data['nam_cur']))
                    ->setType($data['typ'])
                ;
            }
        );

        return 0;
    }
}
