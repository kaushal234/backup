<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Directory\PositionClassificationFilterType;
use AppBundle\Form\Type\Directory\Position\PositionCategoryBatchType;
use AppBundle\Form\Type\Directory\Position\PositionCategoryType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/position-categories', defaults: ['alvest_module' => 'ESM', 'breadcrumb_label' => 'menu.position_category.title', 'moduleDomain' => 'position_categories'])]
class PositionCategoryController extends AbstractController
{
    final public const RESOURCE_URL = 'position_categories';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'position_categories_home', methods: ['GET|POST'])]
    #[Template('directory/position_category/list.html.twig')]
    public function index(
        #[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'ASC']]])] HydraCollection $positionCategories,
        #[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'ASC']]])] HydraCollection $divisions,
        Request $request)
    {
        $defaultDivisions = [];
        foreach ($divisions->getSimpleArrayCopy() as $division) {
            $defaultDivisions[$division['id']] = ['iri' => $division['@id'], 'id' => $division['id'], 'name' => $division['name'], 'active' => false];
        }

        $positionCategoriesIndexed = [];
        foreach ($positionCategories as $positionCategory) {
            $positionCategoryId = Iri::id($positionCategory);
            $positionCategoriesIndexed[$positionCategoryId] = $positionCategory->toArray();

            $existingDivisions = $positionCategory['divisions'];
            $positionCategoriesIndexed[$positionCategoryId]['divisions'] = $defaultDivisions;

            foreach ($existingDivisions as $existingDivision) {
                $positionCategoriesIndexed[$positionCategoryId]['divisions'][$existingDivision['id']]['active'] = true;
            }
        }

        $form = $this->createForm(PositionCategoryBatchType::class, ['positionCategories' => $positionCategoriesIndexed]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data['positionCategories'] as $positionCategory) {
                if (null === $positionCategory['positionCategoryType'] || null === $positionCategory['name'] || null === $positionCategory['description']) {
                    continue;
                }

                $originalPositionCategory = $positionCategoriesIndexed[Iri::id($positionCategory)];
                $originalPositionCategory['positionCategoryType'] = $originalPositionCategory['positionCategoryType']['@id'];

                if ($positionCategory === $originalPositionCategory) {
                    continue;
                }

                $positionCategory['divisions'] = array_reduce($positionCategory['divisions'], static function ($memo, $division) {
                    if (false === $division['active']) {
                        return $memo;
                    }
                    $memo[] = $division['iri'];

                    return $memo;
                }, []);

                try {
                    $this->container->get(Client::class)->save(self::RESOURCE_URL, $positionCategory);
                    $this->addFlash(
                        'success',
                        $this->container->get(TranslatorInterface::class)->trans('directory.position_categories.messages.success.edit', [], 'directory')
                    );
                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(false), true);
                    foreach ($errors['violations'] as $error) {
                        $this->addFlash(
                            'warning',
                            $error['message']
                        );
                    }
                }
            }

            return $this->redirectToRoute('position_categories_home');
        }

        return [
            'positionCategories' => $positionCategoriesIndexed,
            'divisions' => $divisions,
            'formFilter' => $this->createForm(PositionClassificationFilterType::class)->createView(),
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/add', name: 'position_categories_add', methods: ['GET', 'POST'])]
    #[Template('directory/position_category/write.html.twig')]
    #[IsGranted('POSITION_CATEGORY_WRITE_VOTER')]
    public function write(Request $request)
    {
        $form = $this->createForm(PositionCategoryType::class, null, ['add' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.position_categories.messages.success.write', [], 'directory'));

                return $this->redirectToRoute('position_categories_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'formFilter' => $this->createForm(PositionClassificationFilterType::class)->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'position_category_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted('POSITION_CATEGORY_WRITE_VOTER')]
    public function delete(Request $request, #[ApiValueResolverAttribute] ApiData $positionCategory): RedirectResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);
        if (!$this->isCsrfTokenValid('delete_position_category', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('position_categories_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $positionCategory['id']);
            $this->addFlash('success', $translator->trans('directory.position_categories.messages.success.delete', [], 'directory'));
        } catch (ClientException $e) {
            $this->addFlash('error', $translator->trans('directory.position_categories.messages.error.delete', [], 'directory'));
        }

        return $this->redirectToRoute('position_categories_home');
    }
}
