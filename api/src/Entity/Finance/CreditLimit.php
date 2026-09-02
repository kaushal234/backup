<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Sales\Customer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['type', 'customer'], message: 'This credit limit type is already defined for this eCustomer.', errorPath: 'customer')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Delete(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['credit_limit']],
    denormalizationContext: ['groups' => ['customer:finance_write']],
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_credit_limit_type_per_customer', columns: ['type', 'customer_id'])]
class CreditLimit
{
    /** @var string */
    final public const FACTOR = 'FACTOR';

    /** @var string */
    final public const INTERNAL_UNITS = 'INTERNAL UNITS';

    /** @var string */
    final public const INTERNAL_SPH = 'INTERNAL SPH';

    /** @var string */
    final public const INTERNAL_OTHERS = 'INTERNAL OTHERS';

    final public const CREDIT_LIMIT_TYPES = [self::FACTOR, self::INTERNAL_UNITS, self::INTERNAL_SPH, self::INTERNAL_OTHERS];

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: self::CREDIT_LIMIT_TYPES)]
    #[Assert\NotNull]
    #[Groups(['credit_limit', 'customer:finance_write'])]
    public string $type;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['credit_limit', 'customer:finance_write'])]
    public int $amount;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['credit_limit', 'customer:finance_write'])]
    public Currency $currency;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'creditLimits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public Customer $customer;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
