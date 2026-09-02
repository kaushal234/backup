<?php

declare(strict_types=1);

namespace AppBundle\Controller\Materials;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Materials\EvendorsNewsType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EvendorsNewsController.
 */
#[Route(path: 'materials/evendors_news', defaults: ['breadcrumb_label' => 'menu.evendors_news.title', 'moduleDomain' => 'evendors_news'])]
class EvendorsNewsController extends AbstractController
{
    final public const EVENDORS_NEWS = 'evendors_news';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            FileStreamedResponseFactory::class,
            Client::class,
            FormFactoryInterface::class,
            ViolationMapper::class,
            TranslatorInterface::class,
            FileManager::class,
        ]);
    }

    /**
     * @return array
     */
    #[Route(path: '', name: 'evendors_news_home', methods: 'GET')]
    #[Template('materials/evendors_news/list.html.twig')]
    public function index(Request $request)
    {
        $parameters = [
            'page' => $request->query->getInt('page', 1),
            'itemsPerPage' => 25,
        ];

        $evendorsNews = $this->container->get(Client::class)->findBy('evendors_news', $parameters, ['publishedAt']);

        return [
            'evendorsNews' => $evendorsNews,
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    /**
     * @return ApiData[]
     */
    #[Route(path: '/{id}/show', name: 'evendors_news_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('materials/evendors_news/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $evendorsNews)
    {
        return [
            'evendorsNews' => $evendorsNews,
        ];
    }

    #[Route(path: '/add', name: 'evendors_news_add', methods: 'GET|POST')]
    #[Template('materials/evendors_news/add.html.twig')]
    #[IsGranted('FEATURE_EVENDORS_NEWS_WRITE')]
    public function add(Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('evendors_news', EvendorsNewsType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $files = $form->get('files')->getData();
                $data = $this->cleanDataForApi($form->getData());

                $evendorsNews = $this->container->get(Client::class)->save(self::EVENDORS_NEWS, $data);

                $this->uploadFiles($evendorsNews, $files);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('news.messages.success.add', [], 'news')
                );

                return $this->redirectToRoute('evendors_news_show', ['id' => $evendorsNews['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                $this->addFlash(
                    'warning',
                    $this->container->get(TranslatorInterface::class)->trans('news.messages.error.add', [], 'news')
                );
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'evendors_news_edit', methods: 'GET|POST')]
    #[Template('materials/evendors_news/edit.html.twig')]
    #[IsGranted('FEATURE_EVENDORS_NEWS_WRITE')]
    public function edit(#[ApiValueResolverAttribute] ApiData $evendorsNews, Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('evendors_news', EvendorsNewsType::class, $evendorsNews);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $files = $form->get('files')->getData();
                $data = $this->cleanDataForApi($form->getData());

                $returnData = $this->container->get(Client::class)->save($evendorsNews->getIri(), $data);

                $this->uploadFiles($returnData, $files);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('news.messages.success.edit', [], 'news')
                );

                return $this->redirectToRoute('evendors_news_show', ['id' => $evendorsNews->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                $evendorsNews = $this->container->get(Client::class)->find(self::EVENDORS_NEWS, $evendorsNews->getIriId());
                $this->addFlash(
                    'warning',
                    $this->container->get(TranslatorInterface::class)->trans('news.messages.success.partial_edit', [], 'news')
                );
            }
        }

        return [
            'evendorsNews' => $evendorsNews,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'evendors_news_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_EVENDORS_NEWS_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove('evendors_news', $id);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('news.messages.success.delete', [], 'news')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('news.messages.error.delete', [], 'news')
            );
        }

        return $this->redirectToRoute('evendors_news_home');
    }

    #[Route(path: '/{evendorsNewsId}/file/{fileId}/delete', name: 'evendors_news_delete_file', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_EVENDORS_NEWS_WRITE')]
    public function deleteFile($evendorsNewsId, $fileId): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(\sprintf('%s/%s/%s', 'evendors_news', $evendorsNewsId, 'files'), $fileId);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('news.files.delete', [], 'news')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('news.files.error.delete', [], 'news')
            );
        }

        return $this->redirectToRoute('evendors_news_edit', ['id' => $evendorsNewsId]);
    }

    #[Route(path: '/{id}/show-file/{fileId}', name: 'evendors_news_show_file', methods: 'GET', requirements: ['id' => '\d+'])]
    public function showFile($id, $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::EVENDORS_NEWS, $id, $fileId));
    }

    private function uploadFiles($object, $files)
    {
        if (\is_array($files) && [] !== $files) {
            foreach ($files as $file) {
                $this->container->get(FileManager::class)->uploadFile(
                    $object,
                    $file,
                    self::EVENDORS_NEWS,
                    null,
                    'files',
                    true
                );
            }
        }
    }

    private function cleanDataForApi($data)
    {
        if (isset($data['files'])) {
            unset($data['files']);
        }

        return $data;
    }
}
