<?php

declare(strict_types=1);

namespace App\ION\Resources\SpartaExtensions\Times;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProcessor\IONDataProcessor;
use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionInput;
use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionOutput;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/time_keepings',
            input: TimeKeepingPostTransactionInput::class,
            output: TimeKeepingPostTransactionOutput::class,
            processor: IONDataProcessor::class
        ),
        new Get(
            requirements: ['id' => '.*'],
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['time_keeping']],
    denormalizationContext: ['groups' => ['time_keeping:write']],
)]
class TimeKeepingPostTransaction
{
    #[ApiProperty(identifier: true)]
    #[Assert\NotNull]
    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public string $employeeNumber;

    #[Assert\NotNull]
    #[Assert\Choice(choices: ['DIRECT', 'INDIRECT', 'END-ACTIVE'])]
    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public string $transactionType;

    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public ?string $task = null;

    #[Groups(['time_keeping:write'])]
    public ?string $orderNumber = null;

    #[Groups(['time_keeping:write'])]
    public ?int $operationNumber = null;

    #[Groups(['time_keeping:write'])]
    public ?string $comment = null;

    #[Groups(['time_keeping:write'])]
    public ?\DateTime $endDate = null;

    #[Groups(['time_keeping:xml'])]
    private \DateTime $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ('DIRECT' === $this->transactionType && (null === $this->orderNumber || null === $this->operationNumber || mb_strlen($this->orderNumber) <= 1)) {
            $context
                ->buildViolation('violations.transaction.direct')
                ->setTranslationDomain('time_keeping')
                ->atPath('orderNumber')
                ->addViolation()
            ;
        }
        if (('INDIRECT' === $this->transactionType) && (null === $this->task || mb_strlen($this->task) < 2)) {
            $context
                ->buildViolation('violations.transaction.indirect')
                ->setTranslationDomain('time_keeping')
                ->atPath('task')
                ->addViolation()
            ;
        }
    }
}
