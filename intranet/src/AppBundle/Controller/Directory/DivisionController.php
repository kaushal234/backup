<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\Division\DivisionType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/divisions', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.division.title', 'moduleDomain' => 'directory_divisions'])]
class DivisionController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class]);
    }

    #[Route(path: '', name: 'directory_divisions_home', methods: 'GET')]
    #[Template('directory/division/list.html.twig')]
    public function list(#[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'asc'], 'normalizationGroups' => ['division:tree']]])] HydraCollection $divisions)
    {
        return ['divisions' => $divisions];
    }

    #[Route(path: '/{id}/show', name: 'directory_divisions_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/division/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $division)
    {
        return [
            'division' => $division,
            'members' => $this->container->get(Client::class)->findBy('people', [
                'businessUnit.region.subDivision.division' => $division->getIri(),
                'hidden' => 0,
                'disabled' => 0,
                'pagination' => 0,
                'normalization_groups_override' => ['people_list'],
            ]),
        ];
    }

    #[Route(path: '/add', name: 'directory_divisions_add', methods: 'GET|POST')]
    #[Template('directory/division/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function add(Request $request, FormFactoryInterface $formFactory, Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $form = $formFactory->createNamed('division', DivisionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $division = $client->save('divisions', $form->getData());
                $this->addFlash('success', $translator->trans('directory.division.messages.success.add', [], 'directory'));

                return $this->redirectToRoute('directory_divisions_show', ['id' => Iri::id($division)]);
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/edit', name: 'directory_divisions_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/division/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function edit(#[ApiValueResolverAttribute] ApiData $division, Request $request, FormFactoryInterface $formFactory, Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $form = $formFactory->createNamed('division', DivisionType::class, $division);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client->save('divisions', $form->getData());
                $this->addFlash('success', $translator->trans('directory.division.messages.success.edit', [], 'directory'));

                return $this->redirectToRoute('directory_divisions_show', ['id' => $division->getIriId()]);
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'division' => $division,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_divisions_delete', methods: ['GET'])]
    public function delete(#[ApiValueResolverAttribute] ApiData $division, Request $request, Client $client, TranslatorInterface $translator): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_division', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('directory_divisions_home');
        }

        try {
            $client->remove('divisions', $division->getIriId());

            $this->addFlash('success', $translator->trans('directory.division.messages.success.delete', [], 'directory'));
        } catch (ClientException $e) {
            $message = $translator->trans('directory.division.messages.errors.delete', [], 'directory');
            if (null !== $e->getResponse()) {
                $message = json_decode($e->getResponse()->getContent(false), true)['hydra:description'] ?? $message;
            }
            $this->addFlash('error', $message);
        }

        return $this->redirectToRoute('directory_divisions_home');
    }
}
