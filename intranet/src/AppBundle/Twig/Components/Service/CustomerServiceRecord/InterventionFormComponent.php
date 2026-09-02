<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\Service\CustomerServiceRecord;

use ApiBundle\Model\ApiData;
use AppBundle\Factory\Service\SurveyCommissioningFactory;
use AppBundle\Form\Type\Service\CustomerServiceRecord\InterventionLiveType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('InterventionFormComponent')]
class InterventionFormComponent
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public array $customerServiceRecord;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly SurveyCommissioningFactory $surveyCommissioningFactory,
    ) {
    }

    public function mount(ApiData $customerServiceRecord): void
    {
        $this->customerServiceRecord = $customerServiceRecord->toArray();
    }

    /**
     * The ComponentWithFormTrait automatically submits the form with $formValues before each re-render (#[PreReRender]).
     * When the status switches to SOLVED, the answerSurveyCustomerServiceRecords fields appear for the first time
     * in the form, but their values in $formValues are empty (initialized from a form view that did not contain these fields).
     * We therefore inject the existing data into $formValues before the automatic submit occurs,
     * so that the answers are pre-populated with the values from the database.
     */
    #[PreReRender]
    public function prepareFormValues(): void
    {
        $status = $this->formValues['status'] ?? null;

        if ('SOLVED' === $status && 'commissioning' === $this->customerServiceRecord['type']) {
            $answerList = $this->surveyCommissioningFactory->createAnswersCollection(
                $this->customerServiceRecord['answerSurveyCustomerServiceRecords']
            );

            $this->formValues['answerSurveyCustomerServiceRecords'] = $answerList;
        }
    }

    protected function instantiateForm(): FormInterface
    {
        $customerServiceRecord = $this->customerServiceRecord;
        $intervention = $customerServiceRecord['openIntervention'];
        $status = $this->formValues['status'] ?? null;

        // Instantiate survey data
        if ('commissioning' === $this->customerServiceRecord['type'] && 'SOLVED' === $status) {
            $intervention['answerSurveyCustomerServiceRecords'] = $this->surveyCommissioningFactory->createAnswersCollection($customerServiceRecord['answerSurveyCustomerServiceRecords']);
        }

        // Instantiate TOC data
        if ('toc' === $this->customerServiceRecord['type'] && 'SOLVED' === $status) {
            $intervention['solveToc'] = isset($this->formValues['solveToc']) && (bool) $this->formValues['solveToc'];
        }

        return $this->formFactory->create(InterventionLiveType::class, $intervention, [
            'customerServiceRecord' => $customerServiceRecord,
        ]);
    }
}
