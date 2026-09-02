<?php

declare(strict_types=1);

namespace App\Tests\Command\Survey;

use App\Command\SurveyEmailingCommand;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Survey\CustomerSurvey;
use App\Entity\Survey\PublishedSurvey;
use App\Notifier\Survey\SurveyNotifier;
use App\Repository\Survey\PublishedSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SurveyEmailingCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:survey:send';

    public function testExecute()
    {
        $publishedSurveyRepositoryMock = $this->getMockBuilder(PublishedSurveyRepository::class)->disableOriginalConstructor()->onlyMethods(['getUnsentSurveys'])->getMock();

        $publishedSurveyRepositoryMock->expects($this->once())->method('getUnsentSurveys')->with(33)->willReturn($this->getFakeSurveys(33));

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(PublishedSurvey::class)->shouldBeCalledTimes(1)->willReturn($publishedSurveyRepositoryMock);
        $entityManagerProphecy->persist(Argument::that(static fn (PublishedSurvey $survey) => $survey->isSent()))->shouldBeCalledTimes(33)->willReturn($publishedSurveyRepositoryMock);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $notifierProphecy = $this->prophesize(SurveyNotifier::class);
        $notifierProphecy->notifyCreation(Argument::type(CustomerSurvey::class))->shouldBeCalledTimes(33);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new SurveyEmailingCommand($entityManagerProphecy->reveal(), $notifierProphecy->reveal(), new ParameterBag(['survey.host' => 'localhost'])));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute(['command' => self::COMMAND, 'batch_size' => 33]);
    }

    private function getFakeSurveys($amount): array
    {
        $extranetUser = new ExtranetUser();
        $extranetUser->setEmail('extr@netus.er');
        $surveys = [];
        for ($i = 1; $i <= $amount; ++$i) {
            $surveys[] = (new CustomerSurvey())
                ->setCustomer($extranetUser)
            ;
        }

        return $surveys;
    }
}
