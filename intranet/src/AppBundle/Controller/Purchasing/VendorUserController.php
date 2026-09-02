<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\VendorUserDataTableType;
use AppBundle\Form\Type\Purchasing\VendorUser\VendorUserType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/vendor-users', defaults: ['alvest_module' => 'VU', 'breadcrumb_label' => 'menu.vendor_user.title', 'moduleDomain' => 'vendor_users'])]
class VendorUserController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const RESOURCE_URL = 'purchasing/vendor_users';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(name: 'vendor_users_home', methods: ['GET', 'POST'])]
    #[Template('purchasing/vendor_user/list.html.twig')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(VendorUserDataTableType::class, VendorUserDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'vendorUsers' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'vendor_users_show')]
    #[Template('purchasing/vendor_user/show.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_VENDOR_USER_WRITE') or is_granted('FEATURE_VENDOR_USER_IMPERSONATE')"))]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/vendor_users'])] ApiData $vendorUser): array
    {
        return ['vendorUser' => $vendorUser];
    }

    #[Route(path: '/{id}/edit', name: 'vendor_users_edit')]
    #[Template('purchasing/vendor_user/edit.html.twig')]
    #[IsGranted('FEATURE_VENDOR_USER_WRITE')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/vendor_users'])] ApiData $vendorUser, Request $request): RedirectResponse|array
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $form = $formFactory->create(VendorUserType::class, $vendorUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $client->put(\sprintf(self::RESOURCE_URL.'/%d', $vendorUser->getIriId()), ['json' => $vendorUser->toArray()]);
                $translator = $this->container->get(TranslatorInterface::class);
                $this->addFlash(
                    'success',
                    $translator->trans('directory.user.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('vendor_users_home', ['id' => $vendorUser['id']]);
            } catch (ClientException $e) {
                $violationMapper = $this->container->get(ViolationMapper::class);
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'vendorUser' => $vendorUser,
            'form' => $form->createView(),
        ];
    }
}
