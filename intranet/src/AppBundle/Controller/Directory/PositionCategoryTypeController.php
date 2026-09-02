<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Directory\PositionClassificationFilterType;
use AppBundle\Form\Type\Directory\Position\PositionCategoryTypeType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/position-category-types', defaults: ['alvest_module' => 'ESM', 'breadcrumb_label' => 'menu.position_category_types.title', 'moduleDomain' => 'position_category_types'])]
class PositionCategoryTypeController extends AbstractController
{
    final public const RESOURCE_URL = 'position_category_types';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'position_category_types_home', methods: ['GET'])]
    #[Template('directory/position_category_type/list.html.twig')]
    public function index(#[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'ASC']]])] HydraCollection $positionCategoryTypes)
    {
        return [
            'positionCategoryTypes' => $positionCategoryTypes,
            'formFilter' => $this->createForm(PositionClassificationFilterType::class)->createView(),
        ];
    }

    #[Route(path: '/add', name: 'position_category_types_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'position_category_types_edit', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'position_category_types_show', methods: ['GET', 'POST'])]
    #[Template('directory/position_category_type/write.html.twig')]
    #[IsGranted('POSITION_CATEGORY_WRITE_VOTER')]
    public function write(Request $request, #[ApiValueResolverAttribute] ?ApiData $positionCategoryType = null)
    {
        $form = $this->createForm(PositionCategoryTypeType::class, $positionCategoryType);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.position_category_types.messages.success.write', [], 'directory'));

                return $this->redirectToRoute('position_category_types_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'positionCategoryType' => $positionCategoryType,
            'formFilter' => $this->createForm(PositionClassificationFilterType::class)->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'position_category_type_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted('POSITION_CATEGORY_WRITE_VOTER')]
    public function delete(Request $request, #[ApiValueResolverAttribute] ApiData $positionCategory): RedirectResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);
        if (!$this->isCsrfTokenValid('delete_position_category_type', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('position_category_types_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $positionCategory['id']);
            $this->addFlash('success', $translator->trans('directory.position_category_types.messages.success.delete', [], 'directory'));
        } catch (ClientException $e) {
            $this->addFlash('error', $translator->trans('directory.position_category_types.messages.error.delete', [], 'directory'));
        }

        return $this->redirectToRoute('position_category_types_home');
    }
}
