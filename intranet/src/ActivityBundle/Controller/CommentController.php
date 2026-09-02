<?php

declare(strict_types=1);

namespace ActivityBundle\Controller;

use ActivityBundle\Form\Type\CommentType;
use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/activities/comments')]
class CommentController extends AbstractController
{
    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    private readonly RouterInterface $router;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->router = $router;
    }

    #[Route(path: '/add', name: 'comment_add', methods: 'GET|POST')]
    #[Template('@ActivityBundle/comment/add.html.twig')]
    public function addComment(Request $request)
    {
        $resourceIriId = $request->query->get('resourceIriId', null);

        if (empty($resourceIriId)) {
            throw $this->createNotFoundException('No resourceIriId provided.');
        }

        $form = $this->formFactory->createNamed('comment', CommentType::class);
        $backUrl = $request->query->get('backUrl') ?: $request->getSession()->get('backUrl_'.$resourceIriId, $this->router->generate('home'));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['resource'] = $resourceIriId;

                $this->client->save('comments', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('comments.success', [], 'messages')
                );

                return $this->redirect($backUrl);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'resourceIriId' => $resourceIriId,
            'form' => $form->createView(),
            'backUrl' => $backUrl,
        ];
    }
}
