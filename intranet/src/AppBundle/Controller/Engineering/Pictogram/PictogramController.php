<?php

declare(strict_types=1);

namespace AppBundle\Controller\Engineering\Pictogram;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Type\Engineering\Pictogram\PictogramDataTableType;
use AppBundle\DataTable\Type\Engineering\Pictogram\PictogramFileDataTableType;
use AppBundle\Form\Type\Engineering\Pictogram\PictogramType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('FEATURE_PICTOGRAM_READ')]
#[Route(path: '/engineering/pictograms', defaults: ['alvest_module' => 'HMI Pictograms', 'breadcrumb_label' => 'menu.pictograms', 'moduleDomain' => 'pictogram'])]
class PictogramController extends AbstractController
{
    public const string RESOURCE_URL = 'engineering/pictograms';
    public const string DELETE_TOKEN = 'delete_pictogram_id';
    public const string DELETE_TOKEN_FILE = 'delete_pictogram_file_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Route(path: '/', name: 'pictogram_home')]
    public function index(
        Request $request,
        DataTableFactoryInterface $dataTableFactory,
    ): Response {
        $datatable = $dataTableFactory->create(PictogramDataTableType::class, PictogramDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        return $this->render('engineering/pictogram/index.html.twig', [
            'pictogramDataTable' => $datatable->createView(),
        ]);
    }

    #[Route(path: '/add', name: 'pictogram_add', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_PICTOGRAM_CREATE')]
    public function create(Request $request): Response
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(PictogramType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $pictogramSave = $client->save(self::RESOURCE_URL, $form->getData());

                $file = $form->get('file')->getData();
                if (null !== $file) {
                    $this->container->get(FileManager::class)->uploadFile($pictogramSave, $file, self::RESOURCE_URL, null, 'picture', false, true);
                }

                $this->addFlash('success', $translator->trans('pictogram.create.success', [], 'engineering_pictogram'));

                return $this->redirectToRoute('pictogram_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('pictogram.create.error', [], 'engineering_pictogram'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('engineering/pictogram/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'pictogram_show', methods: 'GET')]
    public function read(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $pictogram,
        DataTableFactoryInterface $dataTableFactory,
    ): Response {
        $filesDataTable = $dataTableFactory->create(
            PictogramFileDataTableType::class,
            \sprintf(PictogramFileDataTableType::RESOURCE, $pictogram->getIriId())
        );

        return $this->render('engineering/pictogram/show.html.twig', [
            'pictogram' => $pictogram,
            'filesDataTable' => $filesDataTable->createView(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'pictogram_edit', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_PICTOGRAM_UPDATE')]
    public function edit(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $pictogram,
    ): Response {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(PictogramType::class, $pictogram);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            unset($data['files']);
            try {
                $client->save(self::RESOURCE_URL, $data);
                $this->addFlash('success', $translator->trans('pictogram.update.success', [], 'engineering_pictogram'));

                return $this->redirectToRoute('pictogram_show', ['id' => $pictogram->getIriId()]);
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('pictogram.update.error', [], 'engineering_pictogram'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('engineering/pictogram/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'pictogram_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_PICTOGRAM_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $pictogram): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);

        try {
            $client->remove(self::RESOURCE_URL, $pictogram->getIriId());

            $this->addFlash('success', $translator->trans('pictogram.delete.success', [], 'engineering_pictogram'));
        } catch (ClientException $e) {
            $this->addFlash('error', $translator->trans('pictogram.delete.error', [], 'engineering_pictogram'));
        }

        return $this->redirectToRoute('pictogram_home');
    }

    #[Route(path: '/{pictogramId}/files/{id}', name: 'pictogram_files_show', requirements: ['id' => '\d+'], methods: 'GET')]
    public function showFile($pictogramId, $id): Response
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $pictogramId, $id), [], null, true);
    }

    #[Route(path: '/{pictogramId}/files/{id}/delete', name: 'pictogram_files_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_PICTOGRAM_DELETE')]
    public function deleteFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'pictogramId'])]
        ApiData $pictogram, $id,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN_FILE, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('pictogram_show', ['id' => $pictogram->getIriId()]);
        }

        $this->container->get(FileManager::class)->deleteFile($pictogram, self::RESOURCE_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('pictogram_show', ['id' => $pictogram->getIriId()]);
    }

    #[Route(path: '/{id}/files_ajax', name: 'pictogram_files_ajax', methods: 'GET')]
    public function filesAjax(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $pictogram,
        DataTableFactoryInterface $dataTableFactory,
    ): Response {
        $filesDataTable = $dataTableFactory->create(
            PictogramFileDataTableType::class,
            \sprintf(PictogramFileDataTableType::RESOURCE, $pictogram->getIriId())
        );

        return $this->render('engineering/pictogram/tabs/files_ajax.html.twig', [
            'filesDataTable' => $filesDataTable->createView(),
        ]);
    }
}
