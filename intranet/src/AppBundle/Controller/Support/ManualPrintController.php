<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Support\ManualPrintType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManualPrintController extends AbstractController
{
    /** @var string */
    final public const RESOURCE_URL = 'support/manual_prints';

    private readonly Client $client;

    private readonly TranslatorInterface $translator;

    private readonly ViolationMapper $violationMapper;
    private readonly ZipStreamedResponseFactory $zipStreamedResponseFactory;

    public function __construct(Client $client, TranslatorInterface $translator, ViolationMapper $violationMapper, ZipStreamedResponseFactory $zipStreamedResponseFactory)
    {
        $this->client = $client;
        $this->translator = $translator;
        $this->violationMapper = $violationMapper;
        $this->zipStreamedResponseFactory = $zipStreamedResponseFactory;
    }

    #[Route(path: '/support/manuals/{id}/prints', name: 'manual_prints_list', methods: ['GET'])]
    #[Template('support/manual_prints/list.html.twig')]
    public function list(#[ApiValueResolverAttribute(parameters: ['resource' => ManualController::RESOURCE_URL])] ApiData $manual)
    {
        return [
            'manual' => $manual,
            'canEdit' => $this->isGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']),
        ];
    }

    #[Route(path: '/support/manual_prints/{id}/show', name: 'manual_print_show', methods: ['GET'])]
    #[Template('support/manual_print/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manualPrint)
    {
        return ['print' => $manualPrint];
    }

    #[Route(path: '/support/manuals/{id}/prints/add', name: 'manual_print_add', methods: ['GET', 'POST'])]
    #[Template('support/manual_print/add.html.twig')]
    #[IsGranted('FEATURE_PRINTER_WRITE')]
    public function add(Request $request, FormFactoryInterface $formFactory, #[ApiValueResolverAttribute(parameters: ['resource' => ManualController::RESOURCE_URL])] ApiData $manual)
    {
        $form = $formFactory->createNamed('print_form', ManualPrintType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $print = $this->client->save(self::RESOURCE_URL, array_merge(['manual' => $manual['@id']], $form->getData()));
                $this->addFlash(
                    'success',
                    $this->translator->trans('support.manual_print.add.success', [], 'support')
                );

                return $this->redirectToRoute('manual_print_show', ['id' => $print['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'manual' => $manual,
        ];
    }

    #[Route(path: '/public/prints/{id}', name: 'manual_print_download_zip', methods: ['GET'])]
    public function publicDownloadZip($id)
    {
        try {
            return $this->zipStreamedResponseFactory->create(\sprintf('public/'.self::RESOURCE_URL.'/%s', $id), \sprintf('manual_print_%s', $id));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);

            $this->addFlash(
                'error',
                \sprintf('%s %s', $errorDescription['hydra:description'], $this->translator->trans('support.manual_print.max_download_reached', [], 'support'))
            );

            return $this->render('public/public.html.twig');
        }
    }
}
