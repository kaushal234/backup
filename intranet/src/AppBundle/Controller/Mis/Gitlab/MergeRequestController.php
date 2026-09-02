<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\Gitlab;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Filters\Type\Mis\Gitlab\MergeRequestFiltersType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/gitlab', defaults: ['alvest_module' => 'MIS'])]
#[IsGranted('FEATURE_READ_GITLAB_MERGE_REQUEST')]
class MergeRequestController extends AbstractController
{
    final public const string GITLAB_URL = 'gitlab/projects/merge_requests';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ChartBuilderFactory::class,
            Client::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/merge_requests', name: 'mis_gitlab_merge_request_list', methods: 'GET')]
    #[Template('mis/gitlab/merge_request.html.twig')]
    public function index(Request $request)
    {
        $formFilters = $this->createForm(
            MergeRequestFiltersType::class,
            null,
            [
                'action' => $this->generateUrl('mis_gitlab_merge_request_list'),
                'method' => 'GET',
            ]
        );

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = $formFilters->getData();
        }

        $parameters = $parameters ?? [];

        $parameters['pagination'] = false;
        $parameters['merged_after'] ??= (new \DateTimeImmutable('first day of this month'))
            ->setTime(0, 0)
            ->format(\DATE_ATOM);

        $parameters['order'] = [
            'updated_at' => 'desc',
        ];
        try {
            $mergeRequests = $this->container->get(Client::class)->findBy(
                self::GITLAB_URL,
                $parameters
            );
        } catch (\Exception $e) {
            $mergeRequests = [];
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.gitlab.errors.gitlab_api_error', [], 'mis').$e->getMessage());
        }

        $approved = 0;
        foreach ($mergeRequests as $mergeRequest) {
            if (true === $mergeRequest['approved']) {
                ++$approved;
            }
        }

        $chartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('mis.gitlab.graph_title', [], 'mis'))
            ->addYAxis($this->container->get(TranslatorInterface::class)->trans('mis.gitlab.quantity', [], 'mis'))
            ->disableLegend()
        ;
        $chartBuilder->addPlot(
            '',
            $this->container->get(TranslatorInterface::class)->trans('mis.gitlab.approved', [], 'mis'),
            $approved,
        );
        $chartBuilder->addPlot(
            '',
            $this->container->get(TranslatorInterface::class)->trans('mis.gitlab.not_approved', [], 'mis'),
            \count($mergeRequests) - $approved,
        );

        $approvers = [];

        foreach ($mergeRequests as $mergeRequest) {
            foreach ($mergeRequest['approvers'] ?? [] as $approver) {
                if ('' === $approver) {
                    continue;
                }

                $approvers[$approver] = ($approvers[$approver] ?? 0) + 1;
            }
        }

        arsort($approvers);

        $approversChartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('mis.gitlab.graph_approvers_title', [], 'mis'))
            ->addYAxis($this->container->get(TranslatorInterface::class)->trans('mis.gitlab.quantity', [], 'mis'))
            ->disableLegend()
        ;

        foreach ($approvers as $approver => $count) {
            $approversChartBuilder->addPlot('', $approver, $count);
        }

        return [
            'chart' => $chartBuilder->buildConfig(),
            'rate' => 0 === \count($mergeRequests) ? 0 : (int) (($approved / \count($mergeRequests)) * 100),
            'formFilters' => $formFilters->createView(),
            'mergeRequests' => $mergeRequests ?? [],
            'approversChart' => $approversChartBuilder->buildConfig(),
        ];
    }
}
