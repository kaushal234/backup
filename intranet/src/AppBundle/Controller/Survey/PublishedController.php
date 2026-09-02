<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys/{id}/campaigns/{campaignId}/published', defaults: ['alvest_module' => 'SRV'])]
class PublishedController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const PUBLISHED_URL = 'surveys/published';
    final public const CAMPAIGN_URL = 'surveys/campaigns';
    private readonly Client $client;
    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'survey_published_index', methods: 'GET')]
    #[Template('surveys\published\index.html.twig')]
    #[IsGranted('FEATURE_SURVEY_VIEW')]
    public function index(
        #[ApiValueResolverAttribute(parameters: ['resource' => SurveyController::RESOURCE_URL])] ApiData $survey,
        #[ApiValueResolverAttribute(parameters: ['resource' => CampaignController::CAMPAIGN_URL, 'id' => 'campaignId'])] ApiData $campaign,
        Request $request,
    ) {
        if ($campaign['model']['@id'] !== $survey['@id']) {
            throw $this->createNotFoundException();
        }

        if ($request->query->has('seqId')) {
            $seqId = $request->query->get('seqId');
            $seqUrl = $this->generateUrl('legacy_calendar',
                [
                    'm' => ['tasks', 'task', 'view'],
                    'id' => $seqId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addFlash(
                'warning',
                $this->translator->trans(
                    'messages.success.campaign.new_sequence',
                    [
                        '%seqLink%' => "<a href= $seqUrl>".$seqId.'</a>',
                    ],
                    'surveys')
            );

            return $this->redirectToRoute('survey_published_index', [
                'id' => $survey->getIriId(),
                'campaignId' => $campaign->getIriId(),
            ]);
        }

        $publishedSurveys = $this->client->findBy(self::PUBLISHED_URL, ['campaign' => $campaign['@id']]);

        return [
            'survey' => $survey,
            'campaign' => $campaign,
            'publishedSurveys' => $publishedSurveys,
        ];
    }

    #[Route(path: '/{publishedId}/show', name: 'survey_published_show', methods: 'GET')]
    #[Template('surveys\published\show.html.twig')]
    #[IsGranted('FEATURE_SURVEY_VIEW')]
    public function show(
        #[ApiValueResolverAttribute(parameters: ['resource' => SurveyController::RESOURCE_URL])] ApiData $survey,
        #[ApiValueResolverAttribute(parameters: ['resource' => CampaignController::CAMPAIGN_URL, 'id' => 'campaignId'])] ApiData $campaign,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::PUBLISHED_URL, 'id' => 'publishedId'])] ApiData $publishedSurvey,
    ) {
        if ($campaign['model']['@id'] !== $survey['@id']) {
            throw $this->createNotFoundException();
        }

        return [
            'survey' => $survey,
            'campaign' => $campaign,
            'publishedSurvey' => $publishedSurvey,
        ];
    }

    #[Route(path: '/{publishedId}/delete', name: 'survey_published_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_SURVEY_DELETE')]
    public function delete(
        #[ApiValueResolverAttribute(parameters: ['resource' => SurveyController::RESOURCE_URL])] ApiData $survey,
        #[ApiValueResolverAttribute(parameters: ['resource' => CampaignController::CAMPAIGN_URL, 'id' => 'campaignId'])] ApiData $campaign,
    ) {
        if ($campaign['model']['@id'] !== $survey['@id']) {
            throw $this->createNotFoundException();
        }

        try {
            $this->client->remove(static::CAMPAIGN_URL, $campaign->getIriId());

            $this->addFlash(
                'success',
                $this->translator->trans('messages.success.campaign.delete', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('messages.error.campaign.delete', [], 'surveys')
            );
        }

        return $this->redirectToRoute('surveys_show', ['id' => $survey->getIriId()]);
    }
}
