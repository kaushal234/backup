<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales;

use App\Command\Sales\ProductCertificateExpiredCommand;
use App\Entity\Sales\ProductCertificate;
use App\Notifier\Sales\ProductCertificateNotifier;
use App\Repository\Sales\ProductCertificateRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ProductCertificateExpiredCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'api:sales:product_certificates_expired';

    public function testExecute()
    {
        $productCertificateRepositoryMock = $this->getMockBuilder(ProductCertificateRepository::class)->disableOriginalConstructor()->onlyMethods(['findExpired'])->getMock();
        $productCertificateNotifierProphecy = $this->prophesize(ProductCertificateNotifier::class);

        $productCertificateRepositoryMock->expects($this->once())->method('findExpired')->willReturn([$productCertificate = new ProductCertificate()]);
        $productCertificateNotifierProphecy->sendExpiredCertificates([$productCertificate])->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new ProductCertificateExpiredCommand($productCertificateRepositoryMock, $productCertificateNotifierProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
