<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Service\SurveyCustomerServiceRecord\AnswerSurveyCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\QuestionSurveyCustomerServiceRecord;
use App\Filter\ColumnsFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']]),
        new Post(security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')"),
        new Get(forceEager: false),
        new Put(
            denormalizationContext: ['groups' => ['customer_service_record:answer:update', 'customer_service_record:create', 'customer_service_record:detail', 'customer_service_record:update']],
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')",
        ),
    ],
    routePrefix: 'service',
    normalizationContext: [
        'groups' => self::NORMALIZATION_GROUP,
    ],
    denormalizationContext: [
        'groups' => ['customer_service_record:create', 'customer_service_record:detail'],
    ],
)]
#[Legacy\ExtraColumn(column: 'work_type', value: 'Commissioning')]
#[ApiFilter(ColumnsFilter::class)]
class CommissioningCustomerServiceRecord extends AbstractCustomerServiceRecord implements \Stringable
{
    /**
     * @var Collection<AnswerSurveyCustomerServiceRecord>
     */
    #[ORM\OneToMany(mappedBy: 'commissioningCustomerServiceRecord', targetEntity: AnswerSurveyCustomerServiceRecord::class, cascade: ['all'])]
    #[Groups(['customer_service_record:answer', 'customer_service_record:answer:update'])]
    #[MaxDepth(1)]
    private Collection $answerSurveyCustomerServiceRecords;

    public function __construct()
    {
        parent::__construct();
        $this->answerSurveyCustomerServiceRecords = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getAnswerByQuestionName(string $questionName): ?string
    {
        $answer = $this->answerSurveyCustomerServiceRecords->filter(static function ($answer) use ($questionName): bool {
            return $answer->questionSurveyCustomerServiceRecord->name === $questionName;
        })->first();

        return $answer ? $answer->getAnswer() : null;
    }

    /**
     * @return Collection<AnswerSurveyCustomerServiceRecord>
     */
    public function getAnswerSurveyCustomerServiceRecords(): Collection
    {
        return $this->answerSurveyCustomerServiceRecords;
    }

    public function addAnswerSurveyCustomerServiceRecord(AnswerSurveyCustomerServiceRecord $answerSurveyCustomerServiceRecord): self
    {
        $this->answerSurveyCustomerServiceRecords->add($answerSurveyCustomerServiceRecord);
        $answerSurveyCustomerServiceRecord->commissioningCustomerServiceRecord = $this;

        return $this;
    }

    public function removeAnswerSurveyCustomerServiceRecord(AnswerSurveyCustomerServiceRecord $answerSurveyCustomerServiceRecord): self
    {
        $this->answerSurveyCustomerServiceRecords->removeElement($answerSurveyCustomerServiceRecord);

        return $this;
    }

    public function setAnswerSurveyCustomerServiceRecords(Collection $answerSurveyCustomerServiceRecords): self
    {
        $this->answerSurveyCustomerServiceRecords = $answerSurveyCustomerServiceRecords;

        return $this;
    }

    #[Groups(['customer_service_record'])]
    public function getRatingResult()
    {
        $answers = [];
        foreach (QuestionSurveyCustomerServiceRecord::RATING_QUESTIONS as $questionName) {
            $answers[] = $this->getAnswerByQuestionName($questionName);
        }

        return implode('/', $answers);
    }

    public function getLegacyModuleName(): string
    {
        return '';
    }

    public function getLegacyModuleId(): ?int
    {
        return null;
    }
}
