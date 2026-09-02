<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Support\EquipmentRecordAddManualType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/equipment_records', defaults: ['alvest_module' => 'PUBS'])]
class EquipmentRecordManualController extends AbstractController
{
    /** @var string */
    final public const EQUIPMENT_RECORD_URL = 'equipment_records';

    private readonly Client $client;
    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->translator = $translator;
    }

    #[Route(path: '/{id}/manuals', name: 'equipment_record_manuals_list', methods: ['GET'])]
    #[Template('support/equipment_record_manuals/list.html.twig')]
    public function list(#[ApiValueResolverAttribute] ApiData $equipmentRecord)
    {
        return [
            'equipmentRecord' => $equipmentRecord,
        ];
    }

    #[Route(path: '/{id}/manuals/publish', name: 'equipment_record_manuals_publish', methods: ['GET', 'POST'])]
    #[Template('support/equipment_record_manuals/publish.html.twig')]
    public function publish(#[ApiValueResolverAttribute] ApiData $equipmentRecord, Request $request)
    {
        $form = $this->createForm(EquipmentRecordAddManualType::class, [], [
            'publishable' => $equipmentRecord['publishable'],
            'equipmentRecord' => $equipmentRecord,
        ]);

        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();
        foreach ($session->getFlashBag()->get('violationDescription') as $message) {
            $form->addError(new FormError($message));
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ClickableInterface $buttonTestCbom */
                $buttonTestCbom = $form->get('cbom_submit');
                $publishable = $buttonTestCbom->isClicked() ? false : $form->getConfig()->getOption('publishable', false);
                $data = $form->getData();
                $data['mainEquipmentRecord'] = $equipmentRecord->getIri();
                $data['force'] = $publishable;
                if (!$publishable) {
                    $data['secondaryEquipmentRecords'] = [];
                }
                $this->client->save(ManualController::RESOURCE_URL, $data);

                if ($publishable) {
                    $this->addFlash('success', $this->translator->trans('support.equipment_record.publish.success', [], 'support'));

                    return $this->redirectToRoute('equipment_record_manuals_list', ['id' => $equipmentRecord['id']]);
                }
                $this->addFlash('success', $this->translator->trans('support.equipment_record.cbom.success', [], 'support'));
            } catch (ClientException $exception) {
                $errors = $warnings = 0;
                $body = json_decode($exception->getResponse()->getContent(false), true);
                foreach ($body['violations'] ?? [] as $violation) {
                    if (isset($violation['payload']) && isset($violation['payload']['severity'])) {
                        $this->addFlash('violationDescription', $violation['message']);
                        'warning' === $violation['payload']['severity'] ? ++$warnings : ++$errors;
                    }
                }
                if (isset($body['hydra:description']) && (('No CBOM found for this Equipment Record.' === $body['hydra:description']) || 'Access Denied.' === $body['hydra:description'])) {
                    $this->addFlash('error', $body['hydra:description']);
                    ++$errors;
                }
                if ($exception->getResponse()->getStatusCode() >= Response::HTTP_BAD_REQUEST) {
                    $this->addFlash('error', $this->translator->trans('support.equipment_record.cbom.unknown_error', [], 'support'));
                    ++$errors;
                }
                if (0 === $errors) {
                    $this->addFlash('success', $this->translator->trans('support.equipment_record.cbom.success', [], 'support'));
                    if ($warnings > 0) {
                        $this->addFlash('info', $this->translator->trans('support.equipment_record.cbom.non_critical_error', [], 'support'));
                    }
                } else {
                    $this->addFlash('info', $this->translator->trans('support.equipment_record.cbom.critical_error', [], 'support'));
                }

                return $this->redirectToRoute('equipment_record_manuals_publish', ['id' => $equipmentRecord['id']]);
            }
        }

        return [
            'newManualForm' => $form->createView(),
            'equipmentRecord' => $equipmentRecord,
        ];
    }

    #[Route(path: '/{id}/name-plate', name: 'equipment_record_qr_code_plate', methods: ['GET'])]
    #[Template('support/equipment_record_manuals/qr_code_plate.html.twig')]
    public function qrCodePlate(#[ApiValueResolverAttribute] ApiData $equipmentRecord)
    {
        return [
            'equipmentRecord' => $equipmentRecord,
        ];
    }
}
