<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['type']],
    denormalizationContext: ['groups' => []],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'displayedOrder'])]
#[ApiFilter(SearchFilter::class, properties: ['type', 'description'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'description' => 'partial',
    'type' => 'partial',
])]
#[LockedValue(value: Type::SECURITY_HIGH_ATTENTION, propertyPath: 'description')]
class Type implements \Stringable
{
    /** @var string */
    final public const INCIDENT = 'Incident';

    /** @var string */
    final public const REQUEST = 'Request';

    /** @var string */
    final public const IF_1 = 'IF1';

    /** @var string */
    final public const IF_10 = 'IF 10';

    /** @var string */
    final public const IF_100 = 'IF 100';

    /** @var string */
    final public const IF_1000 = 'IF 1000';

    /** @var string */
    final public const SECURITY_HIGH_ATTENTION = 'security_high_attention';

    final public const string SECURITY_INCIDENT_TEMPLATE = '
<p><strong>Answer to the following questions in this ticket:</strong></p>
<br>
<p><b>A - Confirm & Describe:</b></p>
<ul>
<li>1) Is the incident well described by user ?</li>
<li>2) Do I confirm this is a Information security incident ? Why ?</li>
<li>3) Does the incident impact data Confidentiality, Integrity or Availability ?</li>
<li>4) What is the priority of the incident (P3, P2, P1 - see DMS6658 for details) ?</li>
</ul>
<br>
<p><b>B- Analyze and Contain:</b></p>
<ul>
<li>5) Which first containment is applied to protect systems and data and allow MIS investigation (password change, network or traffic isolation, prevent reuse of stolen data during fraud attempts by raising awareness to potential targeted recipients) ? </li>
<li>6) What is the incident origin assumption ?</li>
<li>7) Are evidences collected, relevant and sufficient to confirm the assumption ?</li>
<li>8) Does the priority level need to be revised and escalated (P1 and P2 shall be escalated - see DMS6658 for details) ?</li>
</ul>
<br>
<p>All non-mis user attached files are automatically zipped and protected with the ”Infected" password to ensure that the file is not infecting the MIS computer by mistake."</p>';

    #[ORM\Column(type: 'string')]
    #[Groups(['type'])]
    public string $description;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::INCIDENT, self::REQUEST])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['type'])]
    public string $type;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Choice(choices: [self::IF_1, self::IF_10, self::IF_100, self::IF_1000])]
    #[Groups(['type'])]
    public ?string $indiceFactor = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $displayedOrder = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['type'])]
    private int $id;

    public function __toString(): string
    {
        return \sprintf('%s - %s', $this->type, $this->description);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
