<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\Dashboard;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\User;
use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Factory\Service\SurveyCommissioningFactory;
use AppBundle\Form\Type\Service\CustomerServiceRecord\InterventionType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/dashboard')]
class CustomerServiceRecordFormsController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            FormFactoryInterface::class,
            ViolationMapper::class,
            CustomerServiceRecordPersister::class,
            SurveyCommissioningFactory::class,
            RequestStack::class,
        ]);
    }

    #[Route(path: '/customer-service-dashboard-forms', name: 'dashboard_csr_forms', methods: ['GET|POST'])]
    #[Template('service/dashboard/csr_forms.html.twig')]
    public function home(#[CurrentUser] User $user)
    {
        $requestStack = $this->container->get(RequestStack::class);
        $request = $requestStack->getMainRequest();

        $client = $this->container->get(Client::class);

        $userCustomerServiceRecords = $client->findBy(CustomerServiceRecordPersister::CUSTOMER_SERVICE_RECORD_URL, [
            'technicianPlanned' => [$user->iriId],
            'status' => ['ASSIGNED', 'IN-PROGRESS'],
            'pagination' => false,
        ]);
        $allCustomerServiceRecords = $userCustomerServiceRecords->all();

        if (empty($allCustomerServiceRecords)) {
            $this->addFlash('danger', 'No data Available (To be managed with security)');

            return $this->render('service/dashboard/widget_not_available.html.twig', [
                'title' => 'CSR Planner',
            ]);
        }

        $interventionForms = [];
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $customerServiceRecordsInProgress = array_filter($allCustomerServiceRecords, static function ($csr) {
            return !empty($csr['openIntervention']) && 'STARTED' === $csr['openIntervention']['status'];
        });

        foreach ($customerServiceRecordsInProgress as $csrKey => $customerServiceRecordInProgress) {
            $intervention = $customerServiceRecordInProgress['openIntervention'];
            $intervention['customerServiceRecord'] = $customerServiceRecordInProgress->toArray();
            $enableSurvey = 'commissioning' === $customerServiceRecordInProgress['type'];

            if ($enableSurvey) {
                $answerList = $this->container->get(SurveyCommissioningFactory::class)->createAnswersCollection($customerServiceRecordInProgress['answerSurveyCustomerServiceRecords'] ?? []);
                $intervention['answerSurveyCustomerServiceRecords'] = $answerList;
            }

            $interventionForms[] = $interventionForm = $formFactory->createNamed('intervention_short_'.$customerServiceRecordInProgress['id'], InterventionType::class, $intervention, [
                'enableSurvey' => $enableSurvey,
                'hourMeters' => $customerServiceRecordInProgress['equipmentRecord']['hourMeter'],
            ]);

            $interventionForm->handleRequest($request);

            if ($interventionForm->isSubmitted() && $interventionForm->isValid()) {
                try {
                    $data = $interventionForm->getData();
                    $client->put($customerServiceRecordInProgress['openIntervention']['@id'], [
                        'json' => [
                            'status' => mb_strtoupper($interventionForm->getClickedButton()->getConfig()->getName()),
                            'endedAt' => $data['endedDate'] ?? null,
                        ],
                    ]);

                    if ($enableSurvey) {
                        $customerServiceRecord = $customerServiceRecordInProgress->toArray();

                        $data = array_intersect_key($customerServiceRecord, array_flip(array_keys($interventionForm->all())) + ['@id' => $customerServiceRecord['@id'], 'type' => $customerServiceRecord['type']]);
                        $data['answerSurveyCustomerServiceRecords'] = $interventionForm->getData()['answerSurveyCustomerServiceRecords'];

                        foreach ($data['answerSurveyCustomerServiceRecords'] as $key => $answer) {
                            $data['answerSurveyCustomerServiceRecords'][$key]['questionSurveyCustomerServiceRecord'] = $answer['questionSurveyCustomerServiceRecord']['@id'];
                        }

                        $this->container->get(CustomerServiceRecordPersister::class)->save($data);
                    }

                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('intervention.success.update', [], 'customer_service_record'));
                    // Remove the form from the list of forms to be displayed
                    // We are on a sub-request, so refresh the page will not submit the form again
                    unset($interventionForms[$csrKey]);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $interventionForm);
                }
            }
        }

        return [
            'forms' => array_map(static fn ($interventionForm) => $interventionForm->createView(), $interventionForms),
        ];
    }
}
