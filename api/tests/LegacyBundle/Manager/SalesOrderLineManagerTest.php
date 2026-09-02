<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Manager;

use App\Entity\Sales\Order;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use LegacyBundle\Manager\ModListManager;
use LegacyBundle\Manager\SalesOrderLineManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SalesOrderLineManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testGetSalesOrderLines()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();
        $statement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $expectedSQL = <<<'SQL'
            SELECT sol.* FROM sor_lines sol WHERE parent_id = :sor
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('prepare')
            ->with($expectedSQL)
            ->willReturn($statement)
        ;

        $statement
            ->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $result
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([])
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());
        $manager->getSalesOrderLines((new Order())->setLegacyId(42));
    }

    public function testGetSalesOrderLinesIDs()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $expectedSQL = <<<'SQL'
            SELECT sol.id FROM sor_lines sol WHERE (parent_id = :sor) AND (status NOT IN (:statuses) OR dt_opened < :startOfMonth)
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->with($expectedSQL, [
                'sor' => 42,
                'statuses' => ['PENDING', 'CREATE_PO'],
                'startOfMonth' => (new \DateTime('midnight first day of this month'))->format('Y-m-d'), ]
            )
            ->willReturn(['id' => 'pouet'])
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());
        self::assertSame(['id' => 'pouet'], $manager->getSalesOrderLinesIDs((new Order())->setLegacyId(42)));
    }

    public function testGetOpenedSalesOrderLines()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $expectedSQL = <<<'SQL'
            SELECT sol.* FROM sor_lines sol WHERE (parent_id = :sor) AND (status != :status)
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->with($expectedSQL, ['sor' => 42, 'status' => 'CLOSED'])
            ->willReturn([])
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());
        $manager->getOpenedSalesOrderLines((new Order())->setLegacyId(42));
    }

    public function testIsPreventingOrderClosing()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $expectedSQL = <<<'SQL'
            SELECT sol.* FROM sor_lines sol WHERE (parent_id = :sor) AND (status != :status)
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->with($expectedSQL, ['sor' => 42, 'status' => 'CLOSED'])
            ->willReturn([])
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());

        $manager->isPreventingOrderClosing((new Order())->setLegacyId(42));
    }

    public function testduplicateSalesOrderLines()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $expectedSOLSQL = <<<'SQL'
            INSERT INTO sor_lines SET parent_id = :sor, dt_opened = NOW(), status = :status, bu = :bu, sls_orno = :sls_orno, model = :model, qty = :qty, del_pen = :del_pen, conf_sls = :conf_sls, conf_erp = :conf_erp, delpen_cond = :delpen_cond, wrty_spec = :wrty_spec, wrty_std = :wrty_std, conf_wrty_erp = :conf_wrty_erp, tpay = :tpay, conf_cxo = :conf_cxo, cu_ocur = :cu_ocur, dp_amt = :dp_amt, dp_pc = :dp_pc, parts_inc = :parts_inc, docs_inc = :docs_inc, conf_lc = :conf_lc, notes = :notes, trans = :trans, inco = :inco, inco_loc = :inco_loc, conf_cis = :conf_cis, ctry = :ctry
            SQL;
        $expectedSOLParameters = [
            'sor' => 1_984,
            'status' => 'PENDING',
            'bu' => '',
            'sls_orno' => '',
            'model' => '',
            'qty' => '',
            'del_pen' => '',
            'conf_sls' => '',
            'conf_erp' => '',
            'delpen_cond' => '',
            'wrty_spec' => '',
            'wrty_std' => '',
            'conf_wrty_erp' => '',
            'tpay' => '',
            'conf_cxo' => '',
            'cu_ocur' => '',
            'dp_amt' => '',
            'dp_pc' => '',
            'parts_inc' => '',
            'docs_inc' => '',
            'conf_lc' => '',
            'notes' => '',
            'trans' => '',
            'inco' => '',
            'inco_loc' => '',
            'conf_cis' => '',
            'ctry' => '',
        ];

        $expectedCurrenciesAndRatesSQL = <<<'SQL'
            INSERT INTO mod_lists (parent_id, module, list_name, list_key, list_key2, value, value2)
                SELECT :duplicata, module, list_name, list_key, list_key2, value, value2
                FROM mod_lists WHERE module = 'SOL' AND parent_id = :origin
            SQL;

        $expectedBreakdownSQL = <<<'SQL'
            INSERT INTO sor_opts (parent_id, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris)
                SELECT :duplicata, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris
                FROM sor_opts WHERE parent_id = :origin
            SQL;

        $expectedSorUnitsSQL = <<<'SQL'
            INSERT INTO sor_units (parent_id, short_desc, long_desc, dgt_est, batch_qty, del_early)
                SELECT :duplicata, short_desc, long_desc, dgt_est, batch_qty, "N"
                FROM sor_units WHERE parent_id = :origin
            SQL;

        $sols = [
            [
                'id' => '12',
                'status' => 'CREATE_PO',
                'dt_opened' => '2018-10-01',
                'bu' => 420,
            ],
            [
                'id' => '13',
                'status' => 'IN_PROGRESS',
                'dt_opened' => '2018-10-02',
                'bu' => 540,
            ],
        ];

        $statement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $expectedSQL = <<<'SQL'
            SELECT sol.* FROM sor_lines sol WHERE parent_id = :sor
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::exactly(9))
            ->method('prepare')
            ->withConsecutive(
                [$expectedSQL],
                [$expectedSOLSQL],
                [$expectedCurrenciesAndRatesSQL],
                [$expectedBreakdownSQL],
                [$expectedSorUnitsSQL],
                [$expectedSOLSQL],
                [$expectedCurrenciesAndRatesSQL],
                [$expectedBreakdownSQL],
                [$expectedSorUnitsSQL],
            )
            ->willReturn($statement)
        ;
        $connection
            ->expects(self::exactly(2))
            ->method('lastInsertId')
            ->willReturn(14, 15);

        $statement
            ->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result)
        ;
        $statement
            ->expects(self::exactly(8))
            ->method('executeStatement')
        ;

        $result
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn($sols)
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());
        $manager->duplicateSalesOrderLines((new Order())->setLegacyId(42), (new Order())->setLegacyId(1_984));
    }

    public function testduplicateSalesOrderLinesWithoutLines()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();
        $statement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $expectedSQL = <<<'SQL'
            SELECT sol.* FROM sor_lines sol WHERE parent_id = :sor
            SQL;

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('prepare')
            ->with($expectedSQL)
            ->willReturn($statement)
        ;

        $statement
            ->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $result
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([])
        ;

        $manager = new SalesOrderLineManager($connection, $this->getModListManager());
        $manager->duplicateSalesOrderLines((new Order())->setLegacyId(42), (new Order())->setLegacyId(1_984));
    }

    public function testDeleteSalesOrderLines()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();
        $statement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();

        $expectedSOLDeletionSQL = 'DELETE FROM sor_lines WHERE id = :id';
        $expectedTransactionsDeletionSQL = 'DELETE FROM sor_tran WHERE parent_id = :id';
        $expectedListsDeletionSQL = 'DELETE FROM mod_lists WHERE module = :module AND parent_id = :id';
        $expectedBreakDownDeletionSQL = 'DELETE FROM sor_opts WHERE parent_id = :id';
        $expectedERUpdateSQL = "UPDATE service SET sor_uid = '', buyer_customer_id = '', customer_id = '', customer_name = '', rrd_sso = '', rrd_erp = '' WHERE sor_uid IN (SELECT id FROM sor_units WHERE parent_id = :id)";
        $expectedUnitsDeletionSQL = 'DELETE FROM sor_units WHERE parent_id = :id';
        $sols = [
            ['id' => '12'],
            ['id' => '13'],
        ];

        $connection
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn(new MariaDBPlatform())
        ;

        $connection
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->with('SELECT sol.* FROM sor_lines sol WHERE (parent_id = :sor) AND (status != :status)', ['sor' => 42, 'status' => 'CLOSED'])
            ->willReturn($sols);

        $connection
            ->expects(self::exactly(12))
            ->method('prepare')
            ->withConsecutive(
                [$expectedSOLDeletionSQL],
                [$expectedTransactionsDeletionSQL],
                [$expectedListsDeletionSQL],
                [$expectedBreakDownDeletionSQL],
                [$expectedERUpdateSQL],
                [$expectedUnitsDeletionSQL],
                [$expectedSOLDeletionSQL],
                [$expectedTransactionsDeletionSQL],
                [$expectedListsDeletionSQL],
                [$expectedBreakDownDeletionSQL],
                [$expectedERUpdateSQL],
                [$expectedUnitsDeletionSQL]
            )
            ->willReturn($statement)
        ;

        $statement
            ->expects(self::exactly(12))
            ->method('executeStatement')
        ;
        $manager = new SalesOrderLineManager($connection, $this->getModListManager());

        $manager->deleteSalesOrderLines((new Order())->setLegacyId(42));
    }

    private function getConnection(): MockObject
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $connection
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connection));

        return $connection;
    }

    private function getModListManager(): ModListManager
    {
        return $this->createMock(ModListManager::class);
    }
}
