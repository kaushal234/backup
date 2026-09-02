<?php

declare(strict_types=1);

namespace App\Tests\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\CountriesASMCommand;
use App\Entity\Continent;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesArea;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CountriesASMCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:countries:asm:add';

    /**
     * @dataProvider provideViolations
     */
    public function testCommandSetCountriesASM(array $violations)
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);

        $asm = new People();
        $sso = new Location();
        $continent = new Continent();

        $countries = [new Country(), new Country()];

        foreach ($countries as $country) {
            $continent->addCountry($country);
        }

        $iriConverterProphecy->getResourceFromIri('/people/24')->shouldBeCalledTimes(1)->willReturn($asm);
        $iriConverterProphecy->getResourceFromIri('/locations/12')->shouldBeCalledTimes(1)->willReturn($sso);
        $iriConverterProphecy->getResourceFromIri('/continents/1')->shouldBeCalledTimes(1)->willReturn($continent);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);

        $argument = Argument::that(static fn (SalesArea $salesArea) => $salesArea->getAsm() === $asm && $salesArea->getSso() === $sso && $continent->getCountries()->contains($salesArea->getCountry()));

        $validatorProphecy->validate($argument)->shouldBeCalledTimes(\count($countries))->willReturn(new ConstraintViolationList($violations));

        $entityManagerProphecy->persist($argument)->shouldBeCalledTimes([] === $violations ? \count($countries) : 0);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CountriesASMCommand(
            $iriConverterProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $validatorProphecy->reveal()
        ));
        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'asm' => '/people/24',
            'sso' => '/locations/12',
            'continent' => '/continents/1',
        ]);
    }

    public function provideViolations()
    {
        return [
            'No violations' => [[]],
            'Violations' => [[new ConstraintViolation('pas bon', '', [], null, '', null)]],
        ];
    }
}
