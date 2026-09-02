<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Survey\CampaignType;
use AppBundle\Form\Type\Survey\CustomerSurveyPublicationType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys/{id}/campaigns', defaults: ['alvest_module' => 'SRV'])]
class CampaignController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const PUBLISHED_URL = 'surveys/published';
    final public const CAMPAIGN_URL = 'surveys/campaigns';
    final public const SURVEY_PUBLISH = 'surveys/models/%d/publish';
    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;
    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'survey_campaigns_index', methods: 'GET')]
    #[Template('surveys\campaigns\index.html.twig')]
    #[IsGranted('FEATURE_SURVEY_VIEW')]
    public function index(#[ApiValueResolverAttribute(parameters: ['resource' => SurveyController::RESOURCE_URL])] ApiData $survey)
    {
        $campaigns = $this->client->findBy(self::CAMPAIGN_URL, ['model' => $survey->getIriId()]);

        return [
            'survey' => $survey,
            'campaigns' => $campaigns,
        ];
    }

    #[Route(path: '/add', name: 'survey_published_add', methods: 'GET|POST')]
    #[Template('surveys\campaigns\add.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function add(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => SurveyController::RESOURCE_URL])] ApiData $survey)
    {
        $form = $this->formFactory->createNamed('survey', CustomerSurveyPublicationType::class);

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->has('publish')
            && (null !== $publishButton = $form->get('publish'))
            && $publishButton instanceof ClickableInterface
            && $publishButton->isClicked()
        ) {
            try {
                $campaign = $this->client->save(\sprintf(self::SURVEY_PUBLISH, $survey->getIriId()), [
                    'description' => $form->get('description')->getData(),
                    'targets' => $form->get('extranet_users')->getData(),
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.campaign.create', [], 'surveys')
                );

                return $this->redirectToRoute('legacy_calendar', [
                    'm' => ['seq', 'new', 'sales.survey.campaign.approval'],
                    'campaign' => $campaign['id'],
                    'survey' => $survey->getIriId(),
                ]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render("surveys\campaigns\add.html.twig", [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{campaignId}/edit', name: 'survey_campaign_edit', methods: ['GET|POST'], requirements: ['campaingnId' => '\d+'])]
    #[Template('surveys\campaigns\edit.html.twig')]
    #[IsGranted('FEATURE_SURVEY_EDIT_ADMIN')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::CAMPAIGN_URL, 'id' => 'campaignId'])] ApiData $campaign, Request $request)
    {
        $form = $this->formFactory
            ->createNamed(
                'campaign_form',
                CampaignType::class,
                $campaign
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save(self::CAMPAIGN_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->translator->trans('published.page_titles.edit_success', [], 'surveys')
                );

                return $this->redirectToRoute('survey_campaigns_index', ['id' => $campaign['model']['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'actionPath' => $this->generateUrl('survey_campaign_edit', ['id' => $campaign['model']['id'], 'campaignId' => $campaign['id']]),
            'form' => $form->createView(),
            'campaign' => $campaign,
        ];
    }

    #[Route(path: '/{campaignId}/delete', name: 'survey_campaign_delete', methods: ['GET|DELETE'], requirements: ['campaingnId' => '\d+'])]
    #[IsGranted('FEATURE_SURVEY_DELETE_ADMIN')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::CAMPAIGN_URL, 'id' => 'campaignId'])] ApiData $campaign): RedirectResponse
    {
        try {
            $this->client->remove(self::CAMPAIGN_URL, $campaign['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('published.page_titles.delete_success', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('published.page_titles.delete_error', [], 'surveys')
            );
        }

        return $this->redirectToRoute('survey_campaigns_index', ['id' => $campaign['model']['id']]);
    }
}
