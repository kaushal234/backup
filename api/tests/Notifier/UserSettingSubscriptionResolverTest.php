<?php

declare(strict_types=1);

namespace App\Tests\Notifier;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\UserSetting;
use App\Notifier\UserSettingSubscriptionResolver;
use App\Repository\UserSettingRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class UserSettingSubscriptionResolverTest extends TestCase
{
    /** @var UserSettingRepository&MockObject */
    private UserSettingRepository $userSettingRepository;

    /** @var IriConverterInterface&MockObject */
    private IriConverterInterface $iriConverter;

    private UserSettingSubscriptionResolver $resolver;

    protected function setUp(): void
    {
        $this->userSettingRepository = $this->createMock(UserSettingRepository::class);
        $this->iriConverter = $this->createMock(IriConverterInterface::class);

        $this->resolver = new UserSettingSubscriptionResolver(
            $this->userSettingRepository,
            PropertyAccess::createPropertyAccessor(),
            $this->iriConverter,
        );
    }

    public function testReturnsEmptyArrayWhenNoSettingsFound(): void
    {
        $this->userSettingRepository->method('findByKey')->willReturn([]);

        $result = $this->resolver->findSubscribers('class.subscriptions', new \stdClass());

        self::assertSame([], $result);
    }

    public function testReturnsUserWhenAllScalarFiltersMatch(): void
    {
        $entity = new class {
            public string $status = 'IN_PROGRESS';
            public string $indiceFactor = 'IF 1000';
        };

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting(['status' => ['IN_PROGRESS'], 'indiceFactor' => ['IF 1000']], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity, ['status', 'indiceFactor']);

        self::assertSame([$user], $result);
    }

    public function testSkipsUserWhenOneFilterDoesNotMatch(): void
    {
        $entity = new class {
            public string $status = 'SUSPENDED';
            public string $indiceFactor = 'IF 10';
        };

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting(['status' => ['PENDING', 'IN_PROGRESS'], 'indiceFactor' => ['IF 10']], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity, ['status', 'indiceFactor']);

        self::assertSame([], $result);
    }

    public function testReturnsUserWhenIriFieldMatches(): void
    {
        $location = new Location();
        $entity = new readonly class($location) {
            public function __construct(public Location $location)
            {
            }
        };

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting(['location' => ['/directory/locations/1']], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);
        $this->iriConverter->method('getResourceFromIri')->with('/directory/locations/1')->willReturn($location);

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity);

        self::assertSame([$user], $result);
    }

    public function testSkipsUserWhenFieldNotReadableOnEntity(): void
    {
        $entity = new class {};

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting(['unknownField' => ['value']], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);
        $this->iriConverter->method('getResourceFromIri')->willReturn(new \stdClass());

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity);

        self::assertSame([], $result);
    }

    public function testReturnsUserWhenAnyValueOfMultiValueFieldMatches(): void
    {
        $entity = new class {
            public string $indiceFactor = 'IF 100';
        };

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting(['indiceFactor' => ['IF 10', 'IF 100', 'IF 1000']], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);
        $this->iriConverter->expects(self::never())->method('getResourceFromIri');

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity, ['indiceFactor']);

        self::assertSame([$user], $result);
    }

    public function testFiltersMultipleSettingsIndependently(): void
    {
        $entity = new class {
            public string $status = 'PENDING';
        };

        $matchingUser = $this->createMock(People::class);
        $nonMatchingUser = $this->createMock(People::class);

        $matchingSetting = $this->makeUserSetting(['status' => ['PENDING']], $matchingUser);
        $nonMatchingSetting = $this->makeUserSetting(['status' => ['SOLVED']], $nonMatchingUser);

        $this->userSettingRepository->method('findByKey')->willReturn([$matchingSetting, $nonMatchingSetting]);

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity, ['status']);

        self::assertSame([$matchingUser], $result);
    }

    public function testDoesNotCountSameFieldTwiceWhenMultipleValuesMatch(): void
    {
        $location = new Location();
        $entity = new readonly class($location) {
            public function __construct(
                public Location $location,
                public string $status = 'PENDING',
                public string $indiceFactor = 'IF 10',
            ) {
            }
        };

        $user = $this->createMock(People::class);
        $setting = $this->makeUserSetting([
            'location' => ['/directory/locations/7'],
            'status' => ['PENDING', 'IN_PROGRESS'],
            'indiceFactor' => ['IF 1', 'IF 10'],
        ], $user);

        $this->userSettingRepository->method('findByKey')->willReturn([$setting]);
        $this->iriConverter->method('getResourceFromIri')->with('/directory/locations/7')->willReturn($location);

        $result = $this->resolver->findSubscribers('class.subscriptions', $entity, ['status', 'indiceFactor']);

        self::assertSame([$user], $result);
    }

    private function makeUserSetting(array $settings, People $user): UserSetting
    {
        $userSetting = new UserSetting();
        $userSetting->settings = $settings;
        $userSetting->user = $user;

        return $userSetting;
    }
}
