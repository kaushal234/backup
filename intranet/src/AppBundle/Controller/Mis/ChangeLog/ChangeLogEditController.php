<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ChangeLog;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Mis\ChangeLog\ChangeLogEditType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/change-logs', defaults: ['alvest_module' => 'MIS'])]
class ChangeLogEditController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Client $client,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{id}/edit', name: 'mis_change_logs_edit', methods: 'GET|POST')]
    #[Template('mis/change_logs/edit.html.twig')]
    #[IsGranted(attribute: 'FEATURE_CHANGE_LOG_EDIT')]
    public function index(Request $request, #[ApiValueResolverAttribute] ApiData $changeLog): RedirectResponse|array
    {
        $form = $this->formFactory->create(ChangeLogEditType::class, $changeLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->put($changeLog['@id'], ['json' => ['message' => $changeLog['message']]]);
                $this->addFlash('success', $this->translator->trans('mis.changelog.message.success.edit', [], 'mis'));

                return $this->redirectToRoute('mis_change_logs_index');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'changeLog' => $changeLog,
        ];
    }
}
