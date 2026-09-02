<?php

declare(strict_types=1);

namespace AppBundle\Controller\HumanResources;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\HumanResources\JobDataTableType;
use AppBundle\Form\Type\HumanResources\JobType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/human-resources/jobs', defaults: ['alvest_module' => 'JOB', 'moduleDomain' => 'human_resources_job'])]
class JobController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    private readonly Client $client;
    private readonly TranslatorInterface $translator;
    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;

    public function __construct(Client $client, TranslatorInterface $translator, FormFactoryInterface $formFactory, ViolationMapper $violationMapper)
    {
        $this->client = $client;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
    }

    #[Route(path: '', name: 'human_resources_job_home', methods: ['GET', 'POST'])]
    #[Template('human_resources/job/list.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(JobDataTableType::class, JobDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'jobDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'human_resources_job_show', methods: 'GET')]
    #[Template('human_resources/job/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $job)
    {
        return [
            'job' => $job,
        ];
    }

    #[Route(path: '/add', name: 'human_resources_job_add', methods: 'GET|POST')]
    #[Template('human_resources/job/write.html.twig')]
    #[IsGranted('FEATURE_JOB_WRITE')]
    public function add(Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'job_form',
                JobType::class
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $job = $this->client->save('jobs', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('jobs.add.success', [], 'job')
                );

                return $this->redirectToRoute('human_resources_job_show', ['id' => Iri::id($job)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'actionPath' => $this->generateUrl('human_resources_job_add'),
            'form' => $form->createView(),
            'formTitle' => 'jobs.add.title',
        ];
    }

    #[Route(path: '/{id}/edit', name: 'human_resources_job_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('human_resources/job/write.html.twig')]
    #[IsGranted('FEATURE_JOB_WRITE')]
    public function edit(#[ApiValueResolverAttribute] ApiData $job, Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'job_form',
                JobType::class,
                $job
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $this->client->save('jobs', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('jobs.edit.success', [], 'job')
                );

                return $this->redirectToRoute('human_resources_job_show', ['id' => $job->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'job' => $job,
            'actionPath' => $this->generateUrl('human_resources_job_edit', ['id' => $job->getIriId()]),
            'form' => $form->createView(),
            'formTitle' => 'jobs.edit.title',
        ];
    }

    #[Route(path: '/{id}/delete', name: 'human_resources_job_delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('FEATURE_JOB_WRITE')]
    public function delete(#[ApiValueResolverAttribute] ApiData $job): RedirectResponse
    {
        try {
            $this->client->remove('jobs', $job['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('jobs.delete.success', [], 'job')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('jobs.delete.error', [], 'job')
            );

            return $this->redirectToRoute('human_resources_job_show', ['id' => $job->getIriId()]);
        }

        return $this->redirectToRoute('human_resources_job_home');
    }
}
