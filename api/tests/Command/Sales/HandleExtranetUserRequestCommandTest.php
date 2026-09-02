<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\Sales\ExtranetUser\HandleExtranetUserRequestCommand;
use App\Entity\Sales\ExtranetUser;
use App\Factory\ExtranetUserFactory;
use App\Http\ModuloClient;
use App\Manager\Sales\ExtranetUserManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HandleExtranetUserRequestCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:extranet_user:handle';
    /**
     * @var string
     */
    final public const JSON_WITH_NO_EXITING_EMAIL = '{
        "id": "4224",
        "dt": "2024-01-01 12:21:42",
        "status": "PENDING",
        "form_type": "EXTRANET_USER_REQUEST",
        "form_data": {
            "email":"mioumiou@gmail.com",
            "lastname":"Mioumi",
            "firstname":"Ou",
            "address":"666 Place des Grands Hommes, Dizan",
            "direct-phone":"06001020304",
            "phone":"06001020304",
            "mobile":"06001020304",
            "fax":"06001020304",
            "division":"Ligue 1",
            "department":"Bouchonnois",
            "company":"La Company Créole",
            "country":"FU"
        }
    }';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExtranetUserIsCreated()
    {
        /** @var MockObject|ModuloClient $moduloClientMock */
        $moduloClientMock = $this->getMockBuilder(ModuloClient::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MockObject|ExtranetUserManager $extranetUserManagerMock */
        $extranetUserManagerMock = $this->getMockBuilder(ExtranetUserManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|ExtranetUserFactory $extranetUserFactoryMock */
        $extranetUserFactoryMock = $this->getMockBuilder(ExtranetUserFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|LoggerInterface $loggerMock */
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extranetUserMock = $this->getMockBuilder(ExtranetUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extranetUserFactoryMock
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($extranetUserMock);

        $moduloClientMock
            ->expects(self::once())
            ->method('getContactFormSubmitted')
            ->willReturn([json_decode(self::JSON_WITH_NO_EXITING_EMAIL, true)]);

        $extranetUserManagerMock
            ->expects(self::once())
            ->method('handleExtranetUserRequest')
            ->with($extranetUserMock);

        $tester = $this->getCommandTester(
            $extranetUserManagerMock,
            $extranetUserFactoryMock,
            $loggerMock,
            $moduloClientMock
        );

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }

    /**
     * @return CommandTester
     */
    private function getCommandTester(
        ExtranetUserManager $extranetUserManager,
        ExtranetUserFactory $extranetUserFactory,
        LoggerInterface $logger,
        ModuloClient $moduloClient,
    ) {
        $this->application->addCommand(new HandleExtranetUserRequestCommand(
            $extranetUserManager,
            $extranetUserFactory,
            $logger,
            $moduloClient
        ));

        $command = $this->application->find(self::COMMAND);

        return new CommandTester($command);
    }
}
