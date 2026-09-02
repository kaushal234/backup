<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Serializer\Filter\ContextFilter;
use App\Serializer\Filter\PropertyFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Finance\ExchangeRateRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['exchange_rate', 'currency', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_EXCHANGE_RATE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_EXCHANGE_RATE_WRITE')"),
        new Delete(security: "is_granted('FEATURE_EXCHANGE_RATE_WRITE')"),
    ],
    normalizationContext: ['groups' => ['exchange_rate:detail', 'currency', 'expose_legacy']],
    denormalizationContext: ['groups' => ['exchange_rate:write']],
)]
#[UniqueEntity(fields: ['applicatedOn', 'currency', 'type'], message: 'There is already a rate of this type on this currency for this month.', errorPath: 'type')]
#[ORM\Table(name: 'exchange_rates')]
#[ApiFilter(SearchFilter::class, properties: ['currency', 'type', 'currency.name'])]
#[ApiFilter(OrderFilter::class, properties: ['applicatedOn', 'createdAt', 'type', 'currency.name'])]
#[ApiFilter(DateFilter::class, properties: ['applicatedOn'])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(PropertyFilter::class, arguments: ['overrideDefaultProperties' => true, 'whitelist' => ['applicatedOn', 'type', 'rate', 'currency' => ['name']]])]
#[Legacy\Synchronize(table: 'erp_forex2')]
class ExchangeRate implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const TYPE_END_OF_MONTH_RATE = 'END';
    /**
     * @var string
     */
    final public const TYPE_MONTH_AVERAGE_RATE = 'AVG';
    /**
     * @var string
     */
    final public const TYPE_TLD_OFFICIAL_RATE = 'TLD';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['exchange_rate', 'exchange_rate:detail'])]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['exchange_rate:detail'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'date')]
    #[Groups(['exchange_rate', 'exchange_rate:detail', 'exchange_rate:write'])]
    #[Assert\NotNull]
    #[Assert\Type('DateTimeInterface')]
    #[Legacy\Column(column: 'nam_year', transformer: DateTimeToString::class, options: ['format' => 'Y', 'integer' => true])]
    #[Legacy\Column(column: 'nam_month', transformer: DateTimeToString::class, options: ['format' => 'n', 'integer' => true])]
    private \DateTimeInterface $applicatedOn;

    #[ORM\Column(type: 'string', length: 3)]
    #[Groups(['exchange_rate', 'exchange_rate:detail', 'exchange_rate:write'])]
    #[Assert\Choice(choices: [self::TYPE_END_OF_MONTH_RATE, self::TYPE_MONTH_AVERAGE_RATE, self::TYPE_TLD_OFFICIAL_RATE])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'typ')]
    private string $type;

    #[ORM\Column(type: 'decimal', precision: 13, scale: 8)]
    #[Groups(['exchange_rate', 'exchange_rate:detail', 'exchange_rate:write'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0)]
    #[Legacy\Column(column: 'rate')]
    private string $rate;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['exchange_rate', 'exchange_rate:detail', 'exchange_rate:write'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'nam_cur', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private Currency $currency;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getApplicatedOn(): \DateTimeInterface
    {
        return $this->applicatedOn;
    }

    public function setApplicatedOn(\DateTimeInterface $applicatedOn): self
    {
        $this->applicatedOn = $applicatedOn;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ('01' !== $this->applicatedOn->format('d')) {
            $context
                ->buildViolation('This value must be the first day of the month.')
                ->atPath('applicatedOn')
                ->addViolation()
            ;
        }
    }
}
