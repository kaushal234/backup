<?php

declare(strict_types=1);

namespace AppBundle\Controller\News;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\News\NewsListDataTableType;
use AppBundle\Form\Type\News\NewsType;
use AppBundle\Form\Type\ResourceEmailType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NewsController.
 */
#[Route(path: 'news', defaults: ['alvest_module' => 'INN'])]
class NewsController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    final public const searchItemsPerPage = 25;

    public const RESOURCE_URL = 'news';

    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
        private readonly FileManager $fileManager,
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory, )
    {
    }

    #[Route(path: '', name: 'news_index', methods: ['GET', 'POST'])]
    #[Template('news/news/list.html.twig')]
    public function index(Request $request)
    {
        $datatable = $this->createDataTable(NewsListDataTableType::class, NewsListDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'newsListDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'news_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('news/news/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $news)
    {
        return [
            'news' => $news,
        ];
    }

    #[Route(path: '/{id}/email', name: 'news_email', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('news/news/email.html.twig')]
    public function email(#[ApiValueResolverAttribute] ApiData $news, Request $request)
    {
        $emailForm = $this
            ->formFactory
            ->createNamed(
                'resource_email',
                ResourceEmailType::class,
                [
                    'iri' => $news['@id'],
                    'locale' => $request->getLocale(),
                    'subject' => $news['title'],
                    'link' => $this->generateUrl('news_show', ['id' => $news->getIriId()], UrlGeneratorInterface::ABSOLUTE_URL),
                ]
            )
        ;

        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $this->client->post('mailer', ['json' => $emailForm->getData()]);

            $this->addFlash(
                'success',
                $this->translator->trans('success', [
                    '%type%' => 'news',
                    '%title%' => $news['title'],
                ], 'resource_email')
            );

            return $this->redirectToRoute('news_show', ['id' => $news->getIriId()]);
        }

        return [
            'form' => $emailForm->createView(),
            'news' => $news,
        ];
    }

    #[Route(path: '/add', name: 'news_add', methods: 'GET|POST')]
    #[Template('news/news/add.html.twig')]
    #[IsGranted('FEATURE_NEWS_WRITE')]
    public function add(Request $request, CacheInterface $cache)
    {
        $form = $this->formFactory->createNamed('news', NewsType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $news = $this->client->save('news', $data);

                // Refresh banner news
                $this->client->findBy('news', ['banner' => true], [], ['cache' => true, 'reload' => true]);
                try {
                    $cache->delete('latest_news');
                } catch (InvalidArgumentException $e) {
                    // do nothing
                }
                $pictures = $form->get('files')->getData();

                $pictureFiles = $pictures[0]['file'] ?? null;

                if (isset($pictureFiles)) {
                    if (!\is_array($pictureFiles)) {
                        $pictureFiles = [$pictureFiles];
                    }

                    foreach ($pictureFiles as $pictureFile) {
                        $this->fileManager->uploadFile(
                            $news,
                            $pictureFile,
                            'news',
                            null,
                            'picture',
                            true,
                        );
                    }
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('news.messages.success.add', [], 'news')
                );

                return $this->redirectToRoute('news_show', ['id' => Iri::id($news)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/add-category', name: 'news_category_add', methods: 'GET|POST')]
    #[Template('news/news/add_category.html.twig')]
    #[IsGranted('FEATURE_NEWS_WRITE')]
    public function addCategory(Request $request)
    {
        $form = $this->formFactory->createNamed('name', TextType::class, null, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('news_categories', [
                    'name' => $form->getData(),
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('news.messages.success.add_category', [], 'news')
                );

                return $this->redirectToRoute('news_index');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'news_edit', methods: 'GET|POST')]
    #[Template('news/news/edit.html.twig')]
    #[IsGranted('FEATURE_NEWS_WRITE')]
    public function edit($id, Request $request, CacheInterface $cache)
    {
        $news = $this->client->find('news', $id);
        $actualBanner = $news['banner'];

        $news['contentShort'] = null;

        $form = $this->formFactory->createNamed('news', NewsType::class, $news);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save('news', $data);
                try {
                    $cache->delete('latest_news');

                    if ($actualBanner) {
                        $this->client->findBy('news', ['banner' => true], [], ['cache' => true, 'reload' => true]);
                    }
                } catch (InvalidArgumentException $e) {
                    // do nothing
                }
                $currentFormFiles = $form->get('files')->getData();

                foreach ($currentFormFiles as $file) {
                    // Delete file
                    if (isset($file['delete']) && $file['delete']) {
                        $this->fileManager->deleteFile($news, 'news', \sprintf('%s/%s', 'picture', $file['id']));
                        continue;
                    }
                    /*
                     * Checks below  conditions
                     * 1.If the user has not updated/replaced the current image with the new one then skip
                     * 2.Before skipping it checks if the user has updated the image size from the options
                     */
                    if (empty($file['file'])) {
                        if (null !== $file['imageSize'] && isset($file['id'])) {
                            $this->client->post(\sprintf('/files/%s/resize', $file['id']), ['json' => $this->fileManager->getImageDimensions($file['imageSize'])]);
                        }
                        continue;
                    }

                    // Delete the old file before updating to new one , skip for new files as they wont have Ids
                    if (isset($file['id'])) {
                        $this->fileManager->deleteFile($news, 'news', \sprintf('%s/%s', 'picture', $file['id']));
                    }

                    // File Addition
                    foreach ($file['file'] as $uploadedFile) {
                        if ($uploadedFile instanceof UploadedFile) {
                            $this->fileManager->uploadFile(
                                $news,
                                $uploadedFile,
                                'news',
                                null,
                                'picture',
                                false,
                                true,
                            );
                        }
                    }
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('news.messages.success.edit', [], 'news')
                );

                return $this->redirectToRoute('news_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'news' => $news,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'news_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_NEWS_WRITE')]
    public function delete(CacheInterface $cache, $id): RedirectResponse
    {
        $news = $this->client->find('news', $id);
        $actualBanner = $news['banner'];
        try {
            $this->client->remove('news', $id);
            try {
                $cache->delete('latest_news');
                if ($actualBanner) {
                    $this->client->findBy('news', ['banner' => true], [], ['cache' => true, 'reload' => true]);
                }
            } catch (InvalidArgumentException $e) {
                // do nothing
            }

            $this->addFlash(
                'success',
                $this->translator->trans('news.messages.success.delete', [], 'news')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('news.messages.error.delete', [], 'news')
            );
        }

        return $this->redirectToRoute('news_index');
    }

    #[Route(path: '/{newsId}/files/{fileId}', name: 'news_files_show', requirements: ['newsId' => '\d+'], methods: 'GET')]
    public function showFile($newsId, $fileId)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $newsId, $fileId));
    }

    public function lastNews(CacheInterface $cache)
    {
        return $this->render('news/partial/_last_X_news.html.twig',
            [
                'last_news' => $cache->get('latest_news', function (ItemInterface $item) {
                    $item->expiresAfter(3600);

                    return $this->client->findBy('news', ['itemsPerPage' => 5], ['date']);
                }),
            ]
        );
    }
}
