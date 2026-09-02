<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use AppBundle\Form\Type\Directory\Department\DepartmentDeleteType;
use AppBundle\Form\Type\Directory\Department\DepartmentType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/departments', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.department.title', 'moduleDomain' => 'directory_departments'])]
class DepartmentController extends AbstractController
{
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

    #[Route(path: '', name: 'directory_departments_home', methods: 'GET')]
    #[Template('directory/department/list.html.twig')]
    public function list()
    {
        return ['departments' => $this->client->findBy('departments', [], ['name' => 'asc'])];
    }

    #[Route(path: '/{id}/show', name: 'directory_departments_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/department/show.html.twig')]
    public function show($id)
    {
        return ['department' => $this->client->find('departments', $id)];
    }

    #[Route(path: '/add', name: 'directory_departments_add', methods: 'GET|POST')]
    #[Template('directory/department/add.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('department', DepartmentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $department = $this->client->save('departments', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.department.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_departments_show', ['id' => Iri::id($department)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'directory_departments_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/department/edit.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function edit(Request $request, $id)
    {
        $department = $this->client->find('departments', $id);

        $form = $this->formFactory->createNamed('department', DepartmentType::class, $department);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('departments', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.department.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_departments_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'department' => $department,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_departments_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/department/delete.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function delete(Request $request, $id)
    {
        $department = $this->client->find('departments', $id);
        $users = $this->client->findBy('people', ['department' => $department['@id']]);

        $form = $this->formFactory->createNamed('department', DepartmentDeleteType::class, $department, [
            'action' => $this->generateUrl('directory_departments_delete', ['id' => Iri::id($department)]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $body = [];
                if (true === $department['used']) {
                    $body['replacement'] = $form->get('replacement')->getData();
                }
                $this->client->remove('departments', $id, ['json' => $body]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.department.messages.success.delete', [], 'directory')
                );

                return $this->redirectToRoute('directory_departments_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'department' => $department,
            'used' => $users->count() > 0,
            'form' => $form->createView(),
        ];
    }
}
