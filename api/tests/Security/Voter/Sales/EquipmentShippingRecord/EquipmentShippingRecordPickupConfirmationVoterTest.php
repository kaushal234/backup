<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Security\Voter\Sales\EquipmentShippingRecord\EquipmentShippingRecordPickupConfirmationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EquipmentShippingRecordPickupConfirmationVoterTest extends TestCase
{
    /** @var Security&MockObject */
    private $security;

    /** @var EntityManagerInterface&MockObject */
    private $entityManager;

    /** @var UnitOfWork&MockObject */
    private $unitOfWork;

    /** @var TokenInterface&MockObject */
    private $token;

    private EquipmentShippingRecordPickupConfirmationVoter $voter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->token = $this->createMock(TokenInterface::class);

        $this->entityManager
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);

        /** @var ContainerInterface&MockObject $serviceLocator */
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $serviceLocator
            ->method('get')
            ->willReturnMap([
                [Security::class, $this->security],
                [EntityManagerInterface::class, $this->entityManager],
            ]);

        $serviceLocator
            ->method('has')
            ->willReturnCallback(static function (string $id): bool {
                return \in_array($id, [Security::class, EntityManagerInterface::class], true);
            });

        $this->voter = new EquipmentShippingRecordPickupConfirmationVoter($serviceLocator);
    }

    /**
     * The voter must abstain when the attribute is not supported.
     */
    public function testVoteAbstainOnUnsupportedAttribute(): void
    {
        $subject = $this->createMock(EquipmentShippingRecordLine::class);

        $this->security
            ->expects($this->never())
            ->method('isGranted');

        $this->unitOfWork
            ->expects($this->never())
            ->method('getEntityState');

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $result = $this->voter->vote($this->token, $subject, ['OTHER_ATTRIBUTE']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    /**
     * The voter must abstain when the subject is not an ESR or an ESR line.
     */
    public function testVoteAbstainOnUnsupportedSubject(): void
    {
        $subject = new \stdClass(); // neither ESR nor ESR line

        $this->security
            ->expects($this->never())
            ->method('isGranted');

        $this->unitOfWork
            ->expects($this->never())
            ->method('getEntityState');

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $result = $this->voter->vote(
            $this->token,
            $subject,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    /**
     * If the user has the global MOO_ESR role, access is granted immediately
     * without checking any lines or UnitOfWork.
     */
    public function testVoteGrantedWhenUserHasMooEsrRole(): void
    {
        $line = $this->createMock(EquipmentShippingRecordLine::class);

        // Security is checked once for MOO_ESR and returns true.
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    return 'MOO_ESR' === $attribute;
                }
            );

        // UnitOfWork must not be used at all.
        $this->unitOfWork
            ->expects($this->never())
            ->method('getEntityState');

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $result = $this->voter->vote(
            $this->token,
            $line,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * New ESR line with default value (false):
     * no pickup confirmation change → access is granted,
     * and the feature is never checked.
     */
    public function testVoteGrantedWhenNoChangeOnNewLine(): void
    {
        $line = $this->createMock(EquipmentShippingRecordLine::class);

        $this->unitOfWork
            ->expects($this->once())
            ->method('getEntityState')
            ->with($line)
            ->willReturn(UnitOfWork::STATE_NEW);

        $line->estimatedPickUpDateConfirmation = false;

        // isGranted is called once for MOO_ESR and returns false.
        // FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION must never be checked.
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    return 'MOO_ESR' === $attribute
                        ? false
                        : false;
                }
            );

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $result = $this->voter->vote(
            $this->token,
            $line,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * New ESR line where estimatedPickUpDateConfirmation is true:
     * this is considered a change.
     * If the feature is not granted → access is denied.
     */
    public function testVoteDeniedWhenNewLineWithChangeAndFeatureNotGranted(): void
    {
        $line = $this->createMock(EquipmentShippingRecordLine::class);

        $this->unitOfWork
            ->expects($this->once())
            ->method('getEntityState')
            ->with($line)
            ->willReturn(UnitOfWork::STATE_NEW);

        $line->estimatedPickUpDateConfirmation = true;

        $this->security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    if ('MOO_ESR' === $attribute) {
                        return false;
                    }

                    if ('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION' === $attribute) {
                        return false;
                    }

                    return false;
                }
            );

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $result = $this->voter->vote(
            $this->token,
            $line,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Existing ESR line where estimatedPickUpDateConfirmation changed from false to true:
     * If the feature is not granted : access is denied.
     */
    public function testVoteDeniedWhenExistingLineChangedAndFeatureNotGranted(): void
    {
        $line = $this->createMock(EquipmentShippingRecordLine::class);

        $this->unitOfWork
            ->expects($this->once())
            ->method('getEntityState')
            ->with($line)
            ->willReturn(UnitOfWork::STATE_MANAGED);

        $line->estimatedPickUpDateConfirmation = true;

        $this->unitOfWork
            ->expects($this->once())
            ->method('getOriginalEntityData')
            ->with($line)
            ->willReturn(['estimatedPickUpDateConfirmation' => false]);

        $this->security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    if ('MOO_ESR' === $attribute) {
                        return false;
                    }

                    if ('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION' === $attribute) {
                        return false;
                    }

                    return false;
                }
            );

        $result = $this->voter->vote(
            $this->token,
            $line,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Existing ESR line where estimatedPickUpDateConfirmation changed from false to true:
     * If the feature is granted → access is granted.
     */
    public function testVoteGrantedWhenExistingLineChangedAndFeatureGranted(): void
    {
        $line = $this->createMock(EquipmentShippingRecordLine::class);

        $this->unitOfWork
            ->expects($this->once())
            ->method('getEntityState')
            ->with($line)
            ->willReturn(UnitOfWork::STATE_MANAGED);

        $line->estimatedPickUpDateConfirmation = true;

        $this->unitOfWork
            ->expects($this->once())
            ->method('getOriginalEntityData')
            ->with($line)
            ->willReturn(['estimatedPickUpDateConfirmation' => false]);

        // 1st call: MOO_ESR → false
        // 2nd call: feature → true
        $this->security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    if ('MOO_ESR' === $attribute) {
                        return false;
                    }

                    if ('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION' === $attribute) {
                        return true;
                    }

                    return false;
                }
            );

        $result = $this->voter->vote(
            $this->token,
            $line,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ESR-level update:
     * none of the lines has a pickup confirmation change.
     * The feature is not checked and access is granted.
     */
    public function testVoteGrantedOnEsrWhenNoLineHasChange(): void
    {
        $line1 = $this->createMock(EquipmentShippingRecordLine::class);
        $line2 = $this->createMock(EquipmentShippingRecordLine::class);

        $line1->estimatedPickUpDateConfirmation = false;
        $line2->estimatedPickUpDateConfirmation = false;

        $this->unitOfWork
            ->expects($this->exactly(2))
            ->method('getEntityState')
            ->willReturn(UnitOfWork::STATE_NEW);

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $esr = $this->createMock(EquipmentShippingRecord::class);
        $esr->method('getEquipmentShippingRecordLines')
            ->willReturn([$line1, $line2]);

        // Only MOO_ESR is checked and returns false.
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    return 'MOO_ESR' === $attribute ? false : false;
                }
            );

        $result = $this->voter->vote(
            $this->token,
            $esr,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ESR-level update:
     * at least one line contains a pickup confirmation change.
     * If the feature is not granted → access is denied.
     */
    public function testVoteDeniedOnEsrWhenAtLeastOneLineHasChangeAndFeatureNotGranted(): void
    {
        $lineWithoutChange = $this->createMock(EquipmentShippingRecordLine::class);
        $lineWithChange = $this->createMock(EquipmentShippingRecordLine::class);

        $lineWithoutChange->estimatedPickUpDateConfirmation = false;
        $lineWithChange->estimatedPickUpDateConfirmation = true;

        // Both lines are NEW, so only getEntityState is called.
        $this->unitOfWork
            ->expects($this->exactly(2))
            ->method('getEntityState')
            ->willReturnCallback(static function ($entity) use ($lineWithoutChange, $lineWithChange) {
                if ($entity === $lineWithoutChange || $entity === $lineWithChange) {
                    return UnitOfWork::STATE_NEW;
                }

                return UnitOfWork::STATE_MANAGED;
            });

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $esr = $this->createMock(EquipmentShippingRecord::class);
        $esr->method('getEquipmentShippingRecordLines')
            ->willReturn([$lineWithoutChange, $lineWithChange]);

        // 1st call: MOO_ESR → false
        // 2nd call: feature → false
        $this->security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    if ('MOO_ESR' === $attribute) {
                        return false;
                    }

                    if ('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION' === $attribute) {
                        return false;
                    }

                    return false;
                }
            );

        $result = $this->voter->vote(
            $this->token,
            $esr,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * ESR-level update:
     * at least one line contains a pickup confirmation change.
     * If the feature is granted → access is granted.
     */
    public function testVoteGrantedOnEsrWhenAtLeastOneLineHasChangeAndFeatureGranted(): void
    {
        $lineWithChange = $this->createMock(EquipmentShippingRecordLine::class);
        $lineWithChange->estimatedPickUpDateConfirmation = true;

        $this->unitOfWork
            ->expects($this->once())
            ->method('getEntityState')
            ->with($lineWithChange)
            ->willReturn(UnitOfWork::STATE_NEW);

        $this->unitOfWork
            ->expects($this->never())
            ->method('getOriginalEntityData');

        $esr = $this->createMock(EquipmentShippingRecord::class);
        $esr->method('getEquipmentShippingRecordLines')
            ->willReturn([$lineWithChange]);

        // 1st call: MOO_ESR → false
        // 2nd call: feature → true
        $this->security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                static function (string $attribute): bool {
                    if ('MOO_ESR' === $attribute) {
                        return false;
                    }

                    if ('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION' === $attribute) {
                        return true;
                    }

                    return false;
                }
            );

        $result = $this->voter->vote(
            $this->token,
            $esr,
            [EquipmentShippingRecordPickupConfirmationVoter::EDIT_PICKUP_CONFIRMATION]
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}
