<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\ContractType\ContractTypeType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/contract-types', defaults: ['alvest_module' => 'ESM', 'breadcrumb_label' => 'menu.contract_type.title', 'moduleDomain' => 'contract_type'])]
class ContractTypeController extends AbstractController
{
    /**
     * @var string
     */
    final public const RESOURCE_URL = 'contract_types';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'contract_type_home', methods: 'GET|POST')]
    #[Route(path: '', name: 'contract_type_show', methods: 'GET|POST')]
    #[Template('directory/contract_type/home.html.twig')]
    public function home(#[ApiValueResolverAttribute] HydraCollection $contractTypes, Request $request)
    {
        $form = $this->createForm(ContractTypeType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.contract_type.add.success', [], 'directory'));

                return $this->redirectToRoute('contract_type_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'contractTypes' => $contractTypes,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'contract_type_edit', methods: 'GET|POST')]
    #[Template('directory/contract_type/edit.html.twig')]
    #[IsGranted('CONTRACT_TYPE_ADMIN_VOTER')]
    public function edit(#[ApiValueResolverAttribute] ApiData $contractType, Request $request)
    {
        $form = $this->createForm(ContractTypeType::class, $contractType);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.contract_type.edit.success', [], 'directory'));

                return $this->redirectToRoute('contract_type_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'contractType' => $contractType,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'contract_type_delete', methods: ['GET|DELETE'])]
    #[IsGranted('CONTRACT_TYPE_ADMIN_VOTER')]
    public function delete(#[ApiValueResolverAttribute] ApiData $contractType, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_contract_type', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('directory.contract_type.delete.error_csrf', [], 'directory'));
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $contractType->getIriId());

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('directory.contract_type.delete.success', [], 'directory'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('directory.contract_type.delete.error', [], 'directory'));
        }

        return $this->redirectToRoute('contract_type_home');
    }
}
