<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Support\EquipmentRecordSerialsType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/serials', defaults: ['alvest_module' => 'ER'])]
class EquipmentSerialsController extends AbstractController
{
    /** @var string */
    final public const EQUIPMENT_RECORD_URL = 'equipment_records';
    /** @var string */
    final public const COMPONENT_URL = 'equipment_serial_components';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ViolationMapper::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/{id}/show', name: 'equipment_serials_show', methods: ['GET'])]
    #[Template('support/serials/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $equipmentRecord)
    {
        return [
            'equipmentRecord' => $equipmentRecord,
        ];
    }

    #[Route(path: '/{id}/logs', name: 'equipment_serials_logs', methods: 'GET')]
    #[Template('support/serials/logs.html.twig')]
    public function serialsLogs(#[ApiValueResolverAttribute] ApiData $equipmentRecord)
    {
        return [
            'equipmentRecord' => $equipmentRecord,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'equipment_serials_edit', methods: ['GET', 'POST'])]
    #[Template('support/serials/edit.html.twig')]
    #[IsGranted('FEATURE_EQUIPMENT_SERIAL_ADMIN')]
    public function edit(Request $request, #[ApiValueResolverAttribute] ApiData $equipmentRecord)
    {
        $form = $this->createForm(EquipmentRecordSerialsType::class, $equipmentRecord);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->put(
                    \sprintf('%s/%d', self::EQUIPMENT_RECORD_URL, $equipmentRecord->getIriId()),
                    ['json' => ['serials' => $equipmentRecord['serials']]]
                );
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('support.serials.fields.edition', [], 'support'));

                return $this->redirectToRoute('equipment_serials_show', [
                    'id' => $equipmentRecord->getIriId(),
                ]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'equipmentRecord' => $equipmentRecord,
            'form' => $form->createView(),
        ];
    }
}
