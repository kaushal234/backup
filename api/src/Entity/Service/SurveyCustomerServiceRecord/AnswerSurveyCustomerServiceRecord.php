<?php

declare(strict_types=1);

namespace App\Entity\Service\SurveyCustomerServiceRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
    ],
    routePrefix: 'service',
    normalizationContext: [
        'groups' => ['customer_service_record:answer', 'customer_service_record:answer:detail'],
    ],
    denormalizationContext: [
        'groups' => ['customer_service_record:answer:update'],
    ],
)]
#[Legacy\Synchronize(table: 'mod_kpi')]
#[Legacy\ExtraColumn(column: 'module', value: 'CSR')]
#[Legacy\ExtraColumn(column: 'name', value: 'commissioning')]
class AnswerSurveyCustomerServiceRecord
{
    use LegacyIdentifierTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CommissioningCustomerServiceRecord::class, inversedBy: 'answerSurveyCustomerServiceRecords')]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:update'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    public CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:update'])]
    #[Legacy\Column(column: 'comments')]
    public ?string $comment = '';

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: QuestionSurveyCustomerServiceRecord::class)]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:update'])]
    #[Legacy\Column(column: 'key1', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public QuestionSurveyCustomerServiceRecord $questionSurveyCustomerServiceRecord;

    #[ORM\Column(type: 'datetime')]
    #[Timestampable(on: 'create')]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:detail', 'customer_service_record:answer:update'])]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestampable(on: 'update')]
    #[Groups(['customer_service_record:answer:detail', 'customer_service_record:answer:update'])]
    public ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Blameable(on: 'create')]
    #[Groups(['customer_service_record:answer:detail', 'customer_service_record:answer:update'])]
    public ?People $createdBy = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Blameable(on: 'update')]
    #[Groups(['customer_service_record:answer:detail', 'customer_service_record:answer:update'])]
    public ?People $updatedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:update'])]
    #[Legacy\Column(column: 'val')]
    private ?string $answer;

    public function setAnswer(mixed $answer): void
    {
        if ('false' === $answer || 'true' === $answer) {
            $this->answer = 'true' === $answer ? '1' : '0';

            return;
        }
        $this->answer = (string) $answer;
    }

    public function getAnswer(): string|bool|null
    {
        return $this->answer;
    }
}
