<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer\Directory;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Repository\Directory\PeopleRepository;
use App\Serializer\Normalizer\Directory\PeopleExportNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PeopleExportNormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testNormalizeIncludesDivisionAndIntranetActivationDate(): void
    {
        $division = new Division();
        $division->name = 'North America';

        $subDivision = new SubDivision();
        $subDivision->division = $division;

        $region = new Region();
        $region->setSubDivision($subDivision);

        $businessUnit = new BusinessUnit();
        $businessUnit->setRegion($region);

        $person = new People();
        $person->setBusinessUnit($businessUnit);
        $person->setEnableAt(new \DateTimeImmutable('2026-01-15'));

        $authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);
        $authorizationCheckerProphecy->isGranted('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL')->willReturn(false);
        $authorizationCheckerProphecy->isGranted('AUTHORIZED_APPLICATION_FEATURE_DOWNLOAD_DIRECTORY')->willReturn(false);

        $peopleRepositoryProphecy = $this->prophesize(PeopleRepository::class);

        $normalizer = new PeopleExportNormalizer($authorizationCheckerProphecy->reveal(), $peopleRepositoryProphecy->reveal());
        $normalizer->setNormalizer($this->buildInnerNormalizerStub());

        $result = $normalizer->normalize($person);

        self::assertSame('North America', $result['User Division']);
        self::assertSame('2026-01-15', $result['Intranet Activation Date']);
    }

    public function testNormalizeWithoutDivisionOrEnableAtReturnsEmptyStrings(): void
    {
        $person = new People();

        $authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);
        $authorizationCheckerProphecy->isGranted('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL')->willReturn(false);
        $authorizationCheckerProphecy->isGranted('AUTHORIZED_APPLICATION_FEATURE_DOWNLOAD_DIRECTORY')->willReturn(false);

        $peopleRepositoryProphecy = $this->prophesize(PeopleRepository::class);

        $normalizer = new PeopleExportNormalizer($authorizationCheckerProphecy->reveal(), $peopleRepositoryProphecy->reveal());
        $normalizer->setNormalizer($this->buildInnerNormalizerStub());

        $result = $normalizer->normalize($person);

        self::assertSame('', $result['User Division']);
        self::assertSame('', $result['Intranet Activation Date']);
    }

    private function buildInnerNormalizerStub(): NormalizerInterface
    {
        return new class implements NormalizerInterface {
            public function supportsNormalization($data, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['*' => true];
            }

            public function normalize($object, ?string $format = null, array $context = []): array
            {
                return [
                    'id' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                ];
            }
        };
    }
}
