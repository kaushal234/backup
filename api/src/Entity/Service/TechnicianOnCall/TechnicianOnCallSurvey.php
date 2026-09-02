<?php

declare(strict_types=1);

namespace App\Entity\Service\TechnicianOnCall;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Validator\Constraints\Service\TechnicianOnCall\SurveyCreation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_survey')]
#[ApiResource(
    operations: [
        new Get(openapi: true),
        new GetCollection(normalizationContext: ['groups' => [
            'toc:survey',
            'toc:read',
            'people_public',
            'location',
            'airport_list',
            'customer',
        ]]),
        new Post(
            openapi: true,
            denormalizationContext: ['groups' => ['toc:survey', 'toc:survey:write']],
            security: "is_granted('PUBLIC_ACCESS')",
            securityPostDenormalize: "is_granted('CREATE_TECHNICIAN_ON_CALL_SURVEY', object)",
        ),
        new Put(
            openapi: true,
            security: "is_granted('FEATURE_TECHNICIAN_ON_CALL_SURVEY')",
        ),
    ],
    routePrefix: 'service',
    normalizationContext: ['groups' => ['toc:survey', 'toc:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'technicianOnCall.id',
    'execution',
    'communication',
    'responsiveness',
    'attitude',
    'createdAt',
    'technicianOnCall.assignee',
    'technicianOnCall.salesOrganisationService',
    'technicianOnCall.equipmentRecord.manufacturerLocation',
    'technicianOnCall.airport',
    'technicianOnCall.indiceFactor',
    'technicianOnCall.mainContact',
    'technicianOnCall.customer',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'technicianOnCall.id',
    'execution',
    'createdAt',
    'communication',
    'responsiveness',
    'attitude',
    'technicianOnCall.assignee.lastname',
    'technicianOnCall.salesOrganisationService.name',
    'technicianOnCall.indiceFactor',
    'technicianOnCall.customer.name',
    'technicianOnCall.airport.code',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact'])]
#[SurveyCreation(groups: ['TechnicianOnCallValidation'])]
#[Assert\GroupSequence(['TechnicianOnCallSurvey', 'TechnicianOnCallValidation'])]
#[UniqueEntity('technicianOnCall')]
class TechnicianOnCallSurvey
{
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['toc:survey'])]
    public int $execution;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['toc:survey'])]
    public int $responsiveness;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['toc:survey'])]
    public int $communication;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['toc:survey'])]
    public int $attitude;

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['toc:survey'])]
    public string $comment;

    #[ORM\OneToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'survey')]
    #[ORM\JoinColumn(unique: true, nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['toc:survey', 'toc:read:detail'])]
    #[MaxDepth(1)]
    public TechnicianOnCall $technicianOnCall;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    public People|ExtranetUser $createdBy;

    #[Groups(['toc:survey:write'])]
    public ?string $token = null;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['toc:survey'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    #[Groups(['toc:survey'])]
    public function isPerfect(): bool
    {
        return
            5 === $this->execution
            && 5 === $this->communication
            && 5 === $this->responsiveness
            && 5 === $this->attitude
        ;
    }
}
