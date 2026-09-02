<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Serializer\Denormalizer\MasterData\BusinessPartners\BusinessPartnerDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BusinessPartnerDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testDenormalizeMapsBuyerWhenPresent()
    {
        $data = $this->getBaseData();
        $data['buyer'] = [
            'name' => 'John Doe',
            'employee' => 'EMP001',
            'email' => 'john.doe@tld-europe.com',
        ];

        $capturedBuyer = $this->denormalizeAndCaptureBuyer($data);

        $this->assertSame([
            'fullName' => 'John Doe',
            'employeeCode' => 'EMP001',
            'emailAddress' => 'john.doe@tld-europe.com',
        ], $capturedBuyer);
    }

    public function testDenormalizeSetsBuyerToNullWhenAbsent()
    {
        $data = $this->getBaseData();

        $capturedBuyer = $this->denormalizeAndCaptureBuyer($data);

        $this->assertNull($capturedBuyer);
    }

    private function denormalizeAndCaptureBuyer(array $data): mixed
    {
        $capturedBuyer = false;

        $innerDenormalizer = $this->prophesize(DenormalizerInterface::class);
        $innerDenormalizer
            ->denormalize(Argument::that(static function ($normalized) use (&$capturedBuyer) {
                $capturedBuyer = $normalized['buyer'];

                return true;
            }), BusinessPartner::class, null, Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn(new BusinessPartner());

        $denormalizer = new BusinessPartnerDenormalizer();
        $denormalizer->setDenormalizer($innerDenormalizer->reveal());
        $denormalizer->denormalize($data, BusinessPartner::class);

        return $capturedBuyer;
    }

    private function getBaseData(): array
    {
        return [
            'name' => 'DEUTZ FRANCE SAS',
            'text' => [],
            'code' => 'DEU0016',
        ];
    }
}
