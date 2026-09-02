<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Manager;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Cake\Chronos\Chronos;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskCcManager;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Sequence;
use LegacyBundle\Model\Task;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SequenceManagerTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        Chronos::setTestNow('2099-08-11 00:00:00');
        parent::setUp();
        static::bootKernel();

        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get('validator');
        $this->validator = $validator;
    }

    public function testLegacyInsertQueryIsOKWithValidSequence()
    {
        $connection = $this->getConnection();

        $expectedSQL = <<<'SQL'
            INSERT INTO tasks (module, seq, task, parent_id, status, assignee, assignor, erp, bu_id, date, due_date, close_params, seq_mode, tplno, escalation_trigger, cur_step) VALUES(:module, :seq, :description, :parentId, :status, :assignee, :assignor, :erp, :businessUnit, :date, :dueDate, :closeParams, :mode, :templateNumber, :escalationTrigger, :currentStep)
            SQL;

        $expectedParameters = [
            'module' => 'SEQ',
            'seq' => 'Y',
            'status' => 'OPEN',
            'description' => "Just accept,\n you f&ocirc;&ocirc;l",
            'parentId' => 0,
            'assignee' => 12,
            'assignor' => 24,
            'erp' => 410,
            'businessUnit' => 25,
            'date' => Chronos::parse()->format('Y-m-d H:m:s'),
            'dueDate' => Chronos::parse('+14 days')->format('Y-m-d H:m:s'),
            'closeParams' => 'YTowOnt9',
            'mode' => 'TEMPLATE',
            'templateNumber' => '69',
            'currentStep' => 1,
            'escalationTrigger' => '42',
        ];

        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with($expectedSQL, $expectedParameters);

        $manager = $this->getManager($connection);

        $sequence = new Sequence();

        $location = (new Location())->setErp(410)->setLegacyId(25);

        $sequence
            ->setTemplateName('sales.Customer.Validation')
            ->setDescription("Just accept,\n you fôôl")
            ->setAssignee((new People())->setLegacyId(12))
            ->setAssignor((new People())->setLegacyId(24))
            ->setDate(new \DateTime(Chronos::parse()->toDateString()))
            ->setDueDate(new \DateTime(Chronos::parse('+14 days')->toDateString()))
            ->setLocation($location->setBusinessUnit((new BusinessUnit())->setLocation($location)));

        $manager->insert($sequence);
    }

    public function testLegacyInsertFailWithInvalidSequence()
    {
        $this->expectException(ValidationException::class);

        $connection = $this->getConnection();

        $connection
            ->expects(self::never())
            ->method('executeQuery');

        $manager = $this->getManager($connection, false, false);

        $sequence = new Sequence();

        $manager->insert($sequence);
    }

    private function getConnection(): MockObject&Connection
    {
        /** @var MockObject&Connection $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $connection
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connection));

        $connection
            ->method('lastInsertId')
            ->willReturn('123456');

        return $connection;
    }

    private function getManager(Connection $connection, bool $commentInserted = true, bool $ccAdded = true): MockObject&SequenceManager
    {
        /** @var MockObject&TaskCommentsManager $commentsManager */
        $commentsManager = $this->getMockBuilder(TaskCommentsManager::class)->disableOriginalConstructor()->getMock();
        $commentsManager
            ->expects($commentInserted ? self::once() : self::never())
            ->method('insertComment')
            ->with(self::isInstanceOf(Task::class), SequenceManager::COMMENT_START_OF_SEQUENCE);

        /** @var MockObject&TaskCcManager $taskCcManager */
        $taskCcManager = $this->getMockBuilder(TaskCcManager::class)->disableOriginalConstructor()->getMock();
        $taskCcManager
            ->expects($ccAdded ? self::once() : self::never())
            ->method('addCc')
            ->with(self::isInstanceOf(Task::class));

        /** @var MockObject&SequenceManager $manager */
        $manager = $this
            ->getMockBuilder(SequenceManager::class)
            ->setConstructorArgs([$this->validator, $connection, $commentsManager, $taskCcManager])
            ->onlyMethods(['getTemplate'])
            ->getMock();

        $manager
            ->method('getTemplate')
            ->willReturn(['id' => '69', 'escalation_trigger' => '42', 'short_desc' => 'slip']);

        return $manager;
    }
}
