<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\Customer\CustomerTypeType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/customer-types', defaults: ['alvest_module' => 'ECUST'])]
class CustomerTypeController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/customer_types';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'customer_type_home', methods: ['GET'])]
    #[Template('sales/customer_type/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $customerTypes)
    {
        return ['customerTypes' => $customerTypes];
    }

    #[Route(path: '/add', name: 'customer_type_add', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'customer_type_edit', methods: ['GET|POST'])]
    #[Template('sales/customer_type/write.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_TYPE_WRITE')]
    public function write(Request $request, FormFactoryInterface $formFactory, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $customerType = null)
    {
        $form = $formFactory
            ->createNamed(
                'customer_type_form',
                CustomerTypeType::class,
                $customerType
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customer_types.write.success', [], 'sales_customers')
                );

                return $this->redirectToRoute('customer_type_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'customer_type_delete', methods: ['GET|DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_CUSTOMER_TYPE_WRITE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $customerType): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $customerType->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('customer_types.delete.success', [], 'sales_customers')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('customer_types.delete.success', [], 'sales_customers')
            );

            return $this->redirectToRoute('customer_type_home', ['id' => $customerType->getIriId()]);
        }

        return $this->redirectToRoute('customer_type_home');
    }
}
