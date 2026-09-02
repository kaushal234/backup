<?php

declare(strict_types=1);

namespace App\Tests\DataProcessor\Sales;

use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\DataProcessor\Sales\LeadTimeDataProcessor;
use App\Dto\Manufacturing\LeadTimeBatch;
use App\Entity\Directory\Location;
use App\Entity\Manufacturing\LeadTime;
use App\Entity\Sales\ProductFamily;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LeadTimeDataProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testExceptionIsThrown()
    {
        $processorProhecy = $this->prophesize(ProcessorInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);

        $constraintViolationProphecy = $this->prophesize(ConstraintViolation::class);
        $constraintViolationList = new ConstraintViolationList([$constraintViolationProphecy->reveal()]);

        $leadTimeRight = new LeadTime();
        $leadTimeRight->factory = new Location();
        $leadTimeRight->weeks = 5;
        $leadTimeRight->previousValue = 1;
        $leadTimeRight->productFamily = new ProductFamily();
        $leadTimeRight->description = 'Danses et mets tes baskets';

        $leadTimeWrong = new LeadTime();
        $leadTimeWrong->description = 'should not be good';

        $leadTimeBatch = (new LeadTimeBatch())
            ->addLeadTime($leadTimeRight)
            ->addLeadTime($leadTimeWrong);
        $leadTimeBatch->fullUpdate = true;

        $validatorProphecy->validate($leadTimeBatch)->shouldBeCalledTimes(1)->willReturn($constraintViolationList);
        $securityProphecy->isGranted('LEAD_TIME_WRITE_VOTER', Argument::any())->shouldBeCalledTimes(1)->willReturn(true);
        $entityManagerProphecy->getUnitOfWork()->shouldBeCalledTimes(1)->willReturn($unitOfWorkProphecy->reveal());
        $unitOfWorkProphecy->getOriginalEntityData($leadTimeRight)->shouldBeCalledTimes(1)->willReturn([]);

        $constraintViolationProphecy->getPropertyPath()->shouldBeCalledTimes(2)->willReturn('leadTimes[1].factory');
        $constraintViolationProphecy->getMessage()->shouldBeCalledTimes(1)->willReturn('you did shit');

        $operation = new Post();
        $processorProhecy->process($leadTimeRight, $operation, [], [])->shouldBeCalledTimes(1);
        $processorProhecy->process($leadTimeWrong, $operation, [], [])->shouldNotBeCalled();

        $this->expectException(ValidationException::class);
        $processor = new LeadTimeDataProcessor($processorProhecy->reveal(), $validatorProphecy->reveal(), $securityProphecy->reveal(), $entityManagerProphecy->reveal());

        $processor->process($leadTimeBatch, $operation);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPreviousDataIsSet(array $previousLeadTime, bool $isFullUpdate, int $previousValue)
    {
        $processorProhecy = $this->prophesize(ProcessorInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);

        $constraintViolationProphecy = $this->prophesize(ConstraintViolation::class);
        $constraintViolationList = new ConstraintViolationList([]);

        $leadTime = new LeadTime();
        $leadTime->factory = new Location();
        $leadTime->weeks = 5;
        $leadTime->previousValue = 1;
        $leadTime->productFamily = new ProductFamily();
        $leadTime->description = 'Danses et mets tes baskets';

        $leadTimeBatch = (new LeadTimeBatch())->addLeadTime($leadTime);
        $leadTimeBatch->fullUpdate = $isFullUpdate;

        $validatorProphecy->validate($leadTimeBatch)->shouldBeCalledTimes(1)->willReturn($constraintViolationList);
        $securityProphecy->isGranted('LEAD_TIME_WRITE_VOTER', $leadTime)->shouldBeCalled()->willReturn(true);
        $entityManagerProphecy->getUnitOfWork()->shouldBeCalled()->willReturn($unitOfWorkProphecy->reveal());
        $unitOfWorkProphecy->getOriginalEntityData($leadTime)->shouldBeCalled()->willReturn($previousLeadTime);

        $constraintViolationProphecy->getPropertyPath()->shouldNotBeCalled();
        $constraintViolationProphecy->getMessage()->shouldNotBeCalled();

        $operation = new Post();
        $processorProhecy->process($leadTime, $operation, [], [])->shouldBeCalledTimes(1);
        $persister = new LeadTimeDataProcessor($processorProhecy->reveal(), $validatorProphecy->reveal(), $securityProphecy->reveal(), $entityManagerProphecy->reveal());

        $persister->process($leadTimeBatch, $operation);

        self::assertSame($leadTime->previousValue, $previousValue);
    }

    public function dataProvider(): ?\Generator
    {
        yield 'No previous lead time found' => [[], false, 1];
        yield 'Previous lead time found and FullUpdate is not used' => [['weeks' => 3], false, 3];
        yield 'Weeks of previous lead time is same to the new one and not fullUpdate ' => [['weeks' => 5], false, 1];
        yield 'Weeks of previous lead time is same to the new one and is fullUpdate ' => [['weeks' => 5], true, 5];
    }
}
