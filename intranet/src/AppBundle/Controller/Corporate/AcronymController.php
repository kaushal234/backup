<?php

declare(strict_types=1);

namespace AppBundle\Controller\Corporate;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use AppBundle\Form\Type\Corporate\AcronymDeleteType;
use AppBundle\Form\Type\Corporate\AcronymType;
use AppBundle\Form\Type\SimpleSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Acronym Controller.
 */
#[Route(path: '/acronyms', defaults: ['alvest_module' => 'AGR', 'moduleDomain' => 'acronyms'])]
class AcronymController extends AbstractController
{
    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '', name: 'acronyms_home', methods: 'GET')]
    #[Template('corporate/agr/list.html.twig')]
    public function list()
    {
        $acronyms = $this->client->findBy('acronyms', [], ['acronym' => 'asc']);

        return [
            'acronyms' => $acronyms,
        ];
    }

    #[Route(path: '/add', name: 'acronyms_add', methods: ['GET', 'POST'])]
    #[Template('corporate/agr/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('ACL_ROLE_CEO') or is_granted('ACL_ROLE_COO') or is_granted('ACL_ROLE_CMO') or is_granted('ACL_SUPERUSER')"))]
    public function create(Request $request)
    {
        $form = $this->formFactory->createNamed('acronym', AcronymType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $acronym = $this->client->save('acronyms', $form->getData());

                return $this->redirectToRoute('acronyms_show', ['id' => Iri::id($acronym)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/search', name: 'acronyms_search', methods: ['GET', 'POST'])]
    #[Template('corporate/agr/search.html.twig')]
    public function search(Request $request)
    {
        $simpleSearchForm = $this->formFactory->createNamed('simple_search', SimpleSearchType::class);
        $advancedSearchForm = $this->formFactory->createNamed('advanced_search', AcronymType::class, null, [
            'lax' => true,
        ]);
        $search = [];

        /** @var FormInterface $form */
        foreach ([$simpleSearchForm, $advancedSearchForm] as $form) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $search = $form->getData();

                break;
            }
        }

        $twigVars = [
            'simple_search' => $simpleSearchForm->createView(),
            'advanced_search' => $advancedSearchForm->createView(),
            'acronyms' => [],
        ];

        if ($search) {
            $acronyms = $this->client->search('acronyms', ['query' => $search]);
            $twigVars['acronyms'] = $acronyms;
        }

        return $twigVars;
    }

    #[Route(path: '/{id}', name: 'acronyms_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('corporate/agr/show.html.twig')]
    public function show($id)
    {
        $acronym = $this->client->find('acronyms', $id);

        return [
            'acronym' => $acronym,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'acronyms_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('corporate/agr/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('ACL_ROLE_CEO') or is_granted('ACL_ROLE_COO') or is_granted('ACL_ROLE_CMO') or is_granted('ACL_SUPERUSER')"))]
    public function edit(Request $request, $id)
    {
        $acronym = $this->client->find('acronyms', $id);

        $form = $this->formFactory->createNamed('acronym', AcronymType::class, $acronym);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $acronym = $this->client->save('acronyms', $form->getData());

                return $this->redirectToRoute('acronyms_show', ['id' => Iri::id($acronym)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'acronym' => $acronym,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'acronyms_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('corporate/agr/delete.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('ACL_ROLE_CEO') or is_granted('ACL_ROLE_COO') or is_granted('ACL_ROLE_CMO') or is_granted('ACL_SUPERUSER')"))]
    public function delete(Request $request, $id)
    {
        $acronym = $this->client->find('acronyms', $id);

        $form = $this->formFactory->createNamed('acronyms', AcronymDeleteType::class, $acronym, [
            'action' => $this->generateUrl('acronyms_delete', ['id' => Iri::id($acronym)]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('acronyms', $id);

                $this->addFlash(
                    'success',
                    'The acronym have been deleted.'
                );

                return $this->redirectToRoute('acronyms_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'acronym' => $acronym,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/download', name: 'acronyms_download', methods: 'GET')]
    public function download()
    {
        return $this->fileStreamedResponseFactory->create(
            'acronyms',
            ['query' => ['columns' => 'acronym,shortDescription,description', 'pagination' => false], 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            'acronyms.xlsx',
        );
    }
}
