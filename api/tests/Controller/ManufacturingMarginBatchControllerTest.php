<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Controller\Finance\ManufacturingMarginBatchController;
use App\Dto\Finance\ManufacturingMarginBatch;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ManufacturingMargin;
use App\Factory\ManufacturingMarginBatchFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ManufacturingMarginBatchControllerTest extends TestCase
{
    use ProphecyTrait;

    public function testExceptionIsThrown()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $factoryProphecy = $this->prophesize(ManufacturingMarginBatchFactory::class);

        $request = new Request();
        $file = new UploadedFile(__DIR__.'/../fixtures/file_margins.xlsx', 'test');
        $request->files->set('file', $file);

        $constraintViolationProphecy = $this->prophesize(ConstraintViolation::class);
        $constraintViolationList = new ConstraintViolationList([$constraintViolationProphecy->reveal()]);

        $manufacturingMarginRight = (new ManufacturingMargin())
            ->setCurrency(new Currency())
            ->setEquipmentRecord(new EquipmentRecord())
            ->setActualHours(12)
            ->setStandardHours(13)
            ->setOptionConfigurationParameterHours(14)
            ->setStandardLabourCost(15)
            ->setActualLabourCost(16)
            ->setStandardMaterialCost(17)
            ->setActualMaterialCost(18)
            ->setStandardOtherMaterialCost(19)
            ->setActualOtherMaterialCost(20)
            ->setStandardOtherDirectCost(21)
            ->setActualOtherDirectCost(22)
            ->setFactoryRevenue(23)
            ->setExportedAt(new \DateTime())
            ->setComment('should be good')
        ;

        $manufacturingMarginBatch = (new ManufacturingMarginBatch())
            ->addMargin($manufacturingMarginRight)
            ->addMargin($manufacturingMarginWrong = (new ManufacturingMargin())->setComment('should not be good'));

        $factoryProphecy->createManufacturingMarginBatch($file)->shouldBeCalledOnce()->willReturn($manufacturingMarginBatch);
        $validatorProphecy->validate($manufacturingMarginBatch)->shouldBeCalledTimes(1)->willReturn($constraintViolationList);

        $constraintViolationProphecy->getPropertyPath()->shouldBeCalledTimes(2)->willReturn('margins[1].equipmentRecord');
        $constraintViolationProphecy->getMessage()->shouldBeCalledTimes(1)->willReturn('you did shit');

        $entityManagerProphecy->persist($manufacturingMarginRight)->shouldBeCalledTimes(1);
        $entityManagerProphecy->persist($manufacturingMarginWrong)->shouldNotBeCalled();

        $entityManagerProphecy->flush()->shouldBeCalledOnce();

        $this->expectException(ValidationException::class);
        $controller = new ManufacturingMarginBatchController($factoryProphecy->reveal(), $validatorProphecy->reveal(), $entityManagerProphecy->reveal());

        $controller->__invoke($request);
    }
}
