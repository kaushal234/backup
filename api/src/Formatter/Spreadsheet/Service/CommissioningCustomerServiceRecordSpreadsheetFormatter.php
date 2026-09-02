<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Service;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\SurveyCustomerServiceRecord\QuestionSurveyCustomerServiceRecord;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommissioningCustomerServiceRecordSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'equipmentRecord.salesOrganisation.name' => 'sso',
            'equipmentRecord.salesOrganisationService.name' => 'sso service',
            'equipmentRecord.manufacturerLocation.name' => 'factory',
            'equipmentRecord.endUser' => 'user customer',
            'equipmentRecord.serialNumber' => 'serial number',
            'equipmentRecord.model' => 'model',
            'equipmentRecord.greenTagDate' => 'green tag date',
            'equipmentRecord.dateCommissioned' => 'commissioning date',
            'aspect_comment' => \sprintf('%s %s', $this->translator->trans('intervention.fields.aspect', [], 'customer_service_record'), $this->translator->trans('intervention.fields.comments', [], 'customer_service_record')),
            'conformity_comment' => \sprintf('%s %s', $this->translator->trans('intervention.fields.conformity', [], 'customer_service_record'), $this->translator->trans('intervention.fields.comments', [], 'customer_service_record')),
            'operational_comment' => \sprintf('%s %s', $this->translator->trans('intervention.fields.operational', [], 'customer_service_record'), $this->translator->trans('intervention.fields.comments', [], 'customer_service_record')),
            'shipping_comment' => \sprintf('%s %s', $this->translator->trans('intervention.fields.shipping', [], 'customer_service_record'), $this->translator->trans('intervention.fields.comments', [], 'customer_service_record')),
            'is_link_working_comment' => \sprintf('%s %s', $this->translator->trans('intervention.fields.is_link_working', [], 'customer_service_record'), $this->translator->trans('intervention.fields.comments', [], 'customer_service_record')),
            'aspect' => $this->translator->trans('intervention.fields.aspect', [], 'customer_service_record'),
            'conformity' => $this->translator->trans('intervention.fields.conformity', [], 'customer_service_record'),
            'operational' => $this->translator->trans('intervention.fields.operational', [], 'customer_service_record'),
            'shipping' => $this->translator->trans('intervention.fields.shipping', [], 'customer_service_record'),
            'is_link_working' => $this->translator->trans('intervention.fields.is_link_working', [], 'customer_service_record'),
        ];
    }

    public function getComputedColumns(): array
    {
        return ['technicians', 'aspect_comment', 'conformity_comment', 'operational_comment', 'shipping_comment', 'is_link_working_comment', 'aspect', 'conformity', 'operational', 'shipping', 'is_link_working'];
    }

    /**
     * @param CommissioningCustomerServiceRecord $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'technicians' => implode(' / ', array_map(static fn (Intervention $intervention) => (string) $intervention->leader, $item->getInterventions()->toArray())),
            'aspect',
            'conformity',
            'operational',
            'shipping',
            'is_link_working' => $this->getAnswer($column, $item)['answer'],
            'aspect_comment',
            'conformity_comment',
            'operational_comment',
            'shipping_comment',
            'is_link_working_comment' => $this->getAnswer($column, $item)['comment'],
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return CommissioningCustomerServiceRecord::class === $class;
    }

    private function getAnswer(string $column, CommissioningCustomerServiceRecord $customerServiceRecord): array
    {
        if (str_ends_with($column, '_comment')) {
            $column = str_replace('_comment', '', $column);
        }
        foreach ($customerServiceRecord->getAnswerSurveyCustomerServiceRecords() as $answer) {
            $question = $answer->questionSurveyCustomerServiceRecord;

            if ($column !== $question->name) {
                continue;
            }

            $value = match ($question->type) {
                QuestionSurveyCustomerServiceRecord::CHOICE_TYPE => $this->translator->trans(\sprintf('csr.questions.%s_choices.%s', $question->name, $answer->getAnswer()), [], 'customer_service_record'),
                QuestionSurveyCustomerServiceRecord::BOOLEAN_TYPE => (bool) $answer->getAnswer(),
                default => $answer->getAnswer(),
            };

            return [
                'answer' => $value,
                'comment' => $answer->comment,
            ];
        }

        return [
            'answer' => null,
            'comment' => null,
        ];
    }
}
