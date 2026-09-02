<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\Service\CustomerServiceRecord;

use ApiBundle\Model\ApiData;
use AppBundle\Twig\Components\Service\CustomerServiceRecord\InterventionFormComponent;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class InterventionFormComponentTest extends LiveComponentTestCase
{
    // -------------------------------------------------------------------------
    // mount()
    // -------------------------------------------------------------------------

    public function testMountStoresCustomerServiceRecordAsArray(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture();
        $this->mockApi('service/customer_service_records/1', $csr);

        $component = $this->createLiveComponent(InterventionFormComponent::class, [
            'customerServiceRecord' => new ApiData($csr),
        ])->component();

        self::assertSame($csr, $component->customerServiceRecord);
    }

    // -------------------------------------------------------------------------
    // prepareFormValues() — #[PreReRender]
    // -------------------------------------------------------------------------

    public function testPrepareFormValuesInjectsSurveyAnswersWhenSolvedAndCommissioning(): void
    {
        $this->login('superuser');

        $answers = [
            [
                'questionSurveyCustomerServiceRecord' => ['@id' => '/service/question_survey_customer_service_records/1', 'type' => 'boolean', 'name' => 'q1'],
                'answer' => 'yes',
            ],
        ];

        $csr = $this->csrFixture(1, [
            'type' => 'commissioning',
            'answerSurveyCustomerServiceRecords' => $answers,
        ]);

        $this->mockApi('service/question_survey_customer_service_records', [
            'hydra:member' => [
                ['@id' => '/service/question_survey_customer_service_records/1', 'type' => 'boolean', 'name' => 'q1'],
            ],
        ]);
        $this->mockApi('service/customer_service_records/1', $csr);

        $component = $this->createLiveComponent(InterventionFormComponent::class, [
            'customerServiceRecord' => new ApiData($csr),
        ])->component();

        $component->formValues = ['status' => 'SOLVED'];
        $component->prepareFormValues();

        self::assertArrayHasKey('answerSurveyCustomerServiceRecords', $component->formValues);
        self::assertNotEmpty($component->formValues['answerSurveyCustomerServiceRecords']);
    }

    public function testPrepareFormValuesDoesNothingWhenStatusIsNotSolved(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'type' => 'commissioning',
            'answerSurveyCustomerServiceRecords' => [],
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $component = $this->createLiveComponent(InterventionFormComponent::class, [
            'customerServiceRecord' => new ApiData($csr),
        ])->component();

        $component->formValues = ['status' => 'TO_CONTINUE'];
        $component->prepareFormValues();

        self::assertArrayNotHasKey('answerSurveyCustomerServiceRecords', $component->formValues);
    }

    public function testPrepareFormValuesDoesNothingWhenTypeIsNotCommissioning(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'type' => 'toc',
            'answerSurveyCustomerServiceRecords' => [],
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $component = $this->createLiveComponent(InterventionFormComponent::class, [
            'customerServiceRecord' => new ApiData($csr),
        ])->component();

        $component->formValues = ['status' => 'SOLVED'];
        $component->prepareFormValues();

        self::assertArrayNotHasKey('answerSurveyCustomerServiceRecords', $component->formValues);
    }

    // -------------------------------------------------------------------------
    // instantiateForm() — via le rendu du composant
    // -------------------------------------------------------------------------

    public function testFormIsInstantiatedForStandardCsr(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture();
        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr);
        self::assertNotNull($form);
    }

    public function testFormContainsSurveyFieldsWhenSolvedAndCommissioning(): void
    {
        $this->login('superuser');

        $answers = [
            [
                'questionSurveyCustomerServiceRecord' => ['@id' => '/service/question_survey_customer_service_records/1', 'type' => 'boolean', 'name' => 'q1'],
                'answer' => 'yes',
            ],
        ];

        $csr = $this->csrFixture(1, [
            'type' => 'commissioning',
            'answerSurveyCustomerServiceRecords' => $answers,
            'openIntervention' => array_merge($this->interventionFixture(), [
                'status' => 'STARTED',
                'answerSurveyCustomerServiceRecords' => $answers,
            ]),
        ]);

        $this->mockApi('service/question_survey_customer_service_records', [
            'hydra:member' => [
                ['@id' => '/service/question_survey_customer_service_records/1', 'type' => 'boolean', 'name' => 'q1'],
            ],
        ]);
        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr, ['status' => 'SOLVED']);
        self::assertTrue($form->has('answerSurveyCustomerServiceRecords'));
    }

    public function testFormContainsSolveTocFieldWhenSolvedAndToc(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'type' => 'toc',
            'openIntervention' => array_merge($this->interventionFixture(), [
                'status' => 'STARTED',
                'solveToc' => false,
            ]),
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr, ['status' => 'SOLVED', 'solveToc' => '1']);
        self::assertTrue($form->has('solveToc'));
    }

    public function testFormDoesNotContainSolveTocFieldWhenNotTocType(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, ['type' => 'standard']);
        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr, ['status' => 'SOLVED']);
        self::assertFalse($form->has('solveToc'));
    }

    public function testFormContainsStartedDateWhenInterventionIsPending(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'openIntervention' => array_merge($this->interventionFixture(), ['status' => 'PENDING']),
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr);
        self::assertTrue($form->has('startedDate'));
    }

    public function testFormDoesNotContainStartedDateWhenInterventionIsNotPending(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'openIntervention' => array_merge($this->interventionFixture(), ['status' => 'STARTED']),
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr);
        self::assertFalse($form->has('startedDate'));
    }

    // -------------------------------------------------------------------------
    // instantiateForm() — endedDate (PRE_SET_DATA)
    // -------------------------------------------------------------------------

    public function testFormContainsEndedDateWhenInterventionIsStarted(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'openIntervention' => array_merge($this->interventionFixture(), ['status' => 'STARTED']),
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr);
        self::assertTrue($form->has('endedDate'));
    }

    public function testFormDoesNotContainEndedDateWhenInterventionIsPending(): void
    {
        $this->login('superuser');

        $csr = $this->csrFixture(1, [
            'openIntervention' => array_merge($this->interventionFixture(), ['status' => 'PENDING']),
        ]);

        $this->mockApi('service/customer_service_records/1', $csr);

        $form = $this->callInstantiateForm($csr);
        self::assertFalse($form->has('endedDate'));
    }

    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    private function callInstantiateForm(array $customerServiceRecord, array $formValues = []): \Symfony\Component\Form\FormInterface
    {
        // Instantiate the component directly (bypassing LiveComponent hydration which strips
        // unknown array keys from #[LiveProp] arrays) by resolving dependencies from the container.
        $container = self::getContainer();
        $component = new InterventionFormComponent(
            $container->get(\Symfony\Component\Form\FormFactoryInterface::class),
            $container->get(\AppBundle\Factory\Service\SurveyCommissioningFactory::class),
        );

        // Set customerServiceRecord directly on the property (bypassing mount() which wraps in ApiData)
        // to ensure all fixture keys are preserved as-is.
        $prop = new \ReflectionProperty($component, 'customerServiceRecord');
        $prop->setAccessible(true);
        $prop->setValue($component, $customerServiceRecord);

        $component->formValues = $formValues;

        $ref = new \ReflectionMethod($component, 'instantiateForm');
        $ref->setAccessible(true);

        return $ref->invoke($component);
    }

    private function csrFixture(int $id = 1, array $overrides = []): array
    {
        return array_merge([
            '@id' => \sprintf('/service/customer_service_records/%d', $id),
            'id' => $id,
            'type' => 'standard',
            'answerSurveyCustomerServiceRecords' => [],
            'equipmentRecord' => ['hourMeter' => 0],
            'openIntervention' => $this->interventionFixture(),
        ], $overrides);
    }

    private function interventionFixture(array $overrides = []): array
    {
        return array_merge([
            '@id' => '/service/interventions/1',
            'id' => 1,
            'status' => 'STARTED',
            'hourmeter' => null,
        ], $overrides);
    }
}
