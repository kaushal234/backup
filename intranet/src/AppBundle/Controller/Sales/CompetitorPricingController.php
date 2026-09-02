<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\CompetitorPricingFilterType;
use AppBundle\Form\Type\Sales\CompetitorPricing\CompetitorPricingType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/competitor-pricings', defaults: ['alvest_module' => 'CPR', 'moduleDomain' => 'competitor_pricings'])]
class CompetitorPricingController extends AbstractController
{
    private readonly Client $client;

    private readonly TranslatorInterface $translator;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;
    private readonly FileManager $fileManager;

    public function __construct(Client $client, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, FileStreamedResponseFactory $fileStreamedResponseFactory, FileManager $fileManager)
    {
        $this->client = $client;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->fileManager = $fileManager;
    }

    #[Route(path: '', name: 'competitor_pricings_home')]
    #[Template('sales/competitor_pricings/list.html.twig')]
    public function list(Request $request)
    {
        $parameters = ['order' => ['id' => 'DESC']];

        $formFilter = $this
            ->formFactory
            ->createNamed(
                '',
                CompetitorPricingFilterType::class, [],
                [
                    'action' => $this->generateUrl('competitor_pricings_home'),
                    'method' => 'GET',
                ])
        ;

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            foreach ($formFilter->getData() as $key => $datum) {
                if (!$datum) {
                    continue;
                }
                $parameters[$key] = $datum;
            }
        }

        $competitorPricings = $this->client->findBy('sales/competitor_pricings', $parameters);

        return [
            'competitorPricings' => $competitorPricings,
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'competitor_pricings_show')]
    #[Template('sales/competitor_pricings/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitor_pricings'])] ApiData $competitorPricing, Request $request)
    {
        $form = $this->formFactory->createNamed('competitorPricing', SimpleFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile($competitorPricing, $file, 'sales/competitor_pricings', $form->get('description')->getData(), 'files');
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('customers.messages.success.file', [], 'sales_customers')
                );

                return $this->redirectToRoute('competitor_pricings_show', ['id' => $competitorPricing->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'competitorPricing' => $competitorPricing,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'competitor_pricings_edit', methods: ['GET', 'POST'])]
    #[Template('sales/competitor_pricings/edit.html.twig')]
    public function edit($id, Request $request)
    {
        $iri = \sprintf('/sales/competitor_pricings/%s', $id);

        if (!$this->authorizationChecker->isGranted('FEATURE_COMPETITOR_PRICING_WRITE', $iri)) {
            $this->addFlash('error', $this->translator->trans('competitor_pricings.errors.not_allowed_edit', [], 'competitor_pricings'));

            return $this->redirectToRoute('competitor_pricings_home');
        }

        /** @var ApiData $competitorPricing */
        $competitorPricing = $this->client->get($iri);

        $competitorPricingForm = $this
            ->formFactory
            ->createNamed(
                '',
                CompetitorPricingType::class,
                $competitorPricing
            )
        ;

        $competitorPricingForm->handleRequest($request);
        if ($competitorPricingForm->isSubmitted() && $competitorPricingForm->isValid()) {
            try {
                $data = $competitorPricingForm->getData();

                unset($data['forecastClosure']);

                $this->client->save('sales/competitor_pricings', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('competitor_pricings.messages.edit_success', [], 'competitor_pricings')
                );

                return $this->redirectToRoute('competitor_pricings_show', ['id' => Iri::id($competitorPricing)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $competitorPricingForm);
            }
        }

        return [
            'form' => $competitorPricingForm->createView(),
            'competitorPricing' => $competitorPricing,
        ];
    }

    #[Route(path: '/add', name: 'competitor_pricings_add', methods: ['GET', 'POST'])]
    #[Template('sales/competitor_pricings/add.html.twig')]
    public function add(Request $request)
    {
        if (!$this->authorizationChecker->isGranted('FEATURE_COMPETITOR_PRICING_WRITE')) {
            $this->addFlash('error', $this->translator->trans('competitor_pricings.errors.not_allowed_add', [], 'competitor_pricings'));

            return $this->redirectToRoute('competitor_pricings_home');
        }

        $competitorPricingForm = $this
            ->formFactory
            ->createNamed(
                '',
                CompetitorPricingType::class
            )
        ;

        $competitorPricingForm->handleRequest($request);
        if ($competitorPricingForm->isSubmitted() && $competitorPricingForm->isValid()) {
            try {
                $data = $competitorPricingForm->getData();

                unset($data['forecastClosure']);

                $competitorPricing = $this->client->save('sales/competitor_pricings', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('competitor_pricings.messages.add_success', [], 'competitor_pricings')
                );

                return $this->redirectToRoute('competitor_pricings_show', ['id' => Iri::id($competitorPricing)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $competitorPricingForm);
            }
        }

        return [
            'form' => $competitorPricingForm->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'competitor_pricings_delete', methods: ['GET'])]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitor_pricings'])] ApiData $competitorPricing): RedirectResponse
    {
        if (!$this->authorizationChecker->isGranted('COMPETITOR_PRICING_DELETE_VOTER', $competitorPricing['@id']) && !$this->authorizationChecker->isGranted('MOO_CPR')) {
            $this->addFlash('error', $this->translator->trans('competitor_pricings.messages.not_allowed_delete', [], 'competitor_pricings'));

            return $this->redirectToRoute('competitor_pricings_show', ['id' => $competitorPricing->getIriId()]);
        }

        try {
            $this->client->remove('sales/competitor_pricings', $competitorPricing->getIriId());
            $this->addFlash('success', $this->translator->trans('competitor_pricings.messages.success_delete', [], 'competitor_pricings'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('competitor_pricings.messages.error_delete', [], 'competitor_pricings'));
        }

        return $this->redirectToRoute('competitor_pricings_home');
    }

    #[Route(path: '/{competitorPricingId}/files/{id}/delete', name: 'competitor_pricing_delete_file', methods: ['GET'])]
    #[IsGranted(attribute: 'FEATURE_COMPETITOR_PRICING_WRITE', subject: new Expression('args["competitorPricing"].getIri()'))]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitor_pricings'])] ApiData $competitorPricing, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_competitor_pricing_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('competitor_pricings_show', ['id' => $competitorPricing['id']]);
        }
        $this->fileManager->deleteFile($competitorPricing, 'sales/competitor_pricings', \sprintf('files/%s', $id));

        return $this->redirectToRoute('competitor_pricings_show', ['id' => $competitorPricing['id']]);
    }

    #[Route(path: '/{competitorPricingId}/files/{id}', name: 'competitor_pricing_files_show', methods: 'GET')]
    public function showFile($competitorPricingId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/competitor_pricings/%s/files/%s', $competitorPricingId, $id));
    }
}
