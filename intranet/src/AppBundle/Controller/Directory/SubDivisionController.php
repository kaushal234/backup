<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\SubDivision\SubDivisionType;
use AppBundle\Manager\FileManager;
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

#[Route(path: '/directory/subdivisions', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.sub_division.title', 'moduleDomain' => 'directory_sub_divisions'])]
class SubDivisionController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FileManager::class]);
    }

    #[Route(path: '', name: 'directory_sub_divisions_home', methods: 'GET')]
    #[Template('directory/sub_division/list.html.twig')]
    public function list(#[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'ASC']]])] HydraCollection $subDivisions)
    {
        return ['subDivisions' => $subDivisions];
    }

    #[Route(path: '/{id}/show', name: 'directory_sub_divisions_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/sub_division/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $subDivision)
    {
        return [
            'subDivision' => $subDivision,
            'members' => $this->container->get(Client::class)->findBy('people', [
                'businessUnit.region.subDivision' => $subDivision->getIri(),
                'hidden' => 0,
                'disabled' => 0,
                'pagination' => 0,
                'normalization_groups_override' => ['people_list'],
            ]),
        ];
    }

    #[Route(path: '/add', name: 'directory_sub_divisions_add', methods: 'GET|POST')]
    #[Template('directory/sub_division/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function add(Request $request, FormFactoryInterface $formFactory, Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $form = $formFactory->createNamed('sub_division', SubDivisionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $logoData = $form->get('logo')->getData();
                $subDivision = $client->save('sub_divisions', $form->getData());

                $fileId = $subDivision['logo']['id'] ?? null;
                $this->container->get(FileManager::class)->updateImage($subDivision, 'sub_divisions', $logoData, 'logo', $fileId);

                $this->addFlash('success', $translator->trans('directory.sub_division.messages.success.add', [], 'directory'));

                return $this->redirectToRoute('directory_sub_divisions_show', ['id' => Iri::id($subDivision)]);
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/edit', name: 'directory_sub_divisions_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/sub_division/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function edit(#[ApiValueResolverAttribute] ApiData $subDivision, Request $request, FormFactoryInterface $formFactory, Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $form = $formFactory->createNamed('sub_division', SubDivisionType::class, $subDivision);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $logoData = $form->get('logo')->getData();

                $updatedSubDivision = $client->save('sub_divisions', $form->getData());

                $fileId = $updatedSubDivision['logo']['id'] ?? null;
                $this->container->get(FileManager::class)->updateImage($updatedSubDivision, 'sub_divisions', $logoData, 'logo', $fileId);
                $this->addFlash('success', $translator->trans('directory.sub_division.messages.success.edit', [], 'directory'));

                return $this->redirectToRoute('directory_sub_divisions_show', ['id' => $subDivision->getIriId()]);
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'subDivision' => $subDivision,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_sub_divisions_delete', methods: ['GET'])]
    public function delete(#[ApiValueResolverAttribute] ApiData $subDivision, Request $request, Client $client, TranslatorInterface $translator): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_sub_division', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('directory_sub_divisions_home');
        }

        try {
            $client->remove('sub_divisions', $subDivision->getIriId());

            $this->addFlash('success', $translator->trans('directory.sub_division.messages.success.delete', [], 'directory'));
        } catch (ClientException $e) {
            $message = $translator->trans('directory.division.messages.errors.delete', [], 'directory');
            if (null !== $e->getResponse()) {
                $message = json_decode($e->getResponse()->getContent(false), true)['hydra:description'] ?? $message;
            }
            $this->addFlash('error', $message);
        }

        return $this->redirectToRoute('directory_sub_divisions_home');
    }
}
