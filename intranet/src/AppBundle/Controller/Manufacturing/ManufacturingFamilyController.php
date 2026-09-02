<?php

declare(strict_types=1);

namespace AppBundle\Controller\Manufacturing;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Manufacturing\ManufacturingFamilyType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/catalogue/manufacturing-families', defaults: ['alvest_module' => 'CAT'])]
class ManufacturingFamilyController extends AbstractController
{
    final public const RESOURCE_URL = 'manufacturing/manufacturing_families';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class, FormFactoryInterface::class]);
    }

    #[Route(path: '', name: 'manufacturing_family_home', methods: ['GET'])]
    #[Template('manufacturing/family/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $manufacturingFamilies)
    {
        return ['manufacturingFamily' => $manufacturingFamilies];
    }

    #[Route(path: '/add', name: 'manufacturing_family_add', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'manufacturing_family_edit', methods: ['GET|POST'])]
    #[Template('manufacturing/family/write.html.twig')]
    #[IsGranted('FEATURE_MANUFACTURING_FAMILY_ADMIN')]
    public function write(Request $request, FormFactoryInterface $formFactory)
    {
        $manufacturingFamily = [];
        if ('manufacturing_family_edit' === $request->attributes->get('_route')) {
            $manufacturingFamily = $this->container->get(Client::class)->findOneBy(self::RESOURCE_URL, ['id' => $request->attributes->get('id')]);
        }

        $form = $formFactory
            ->createNamed(
                'manufacturing_family_form',
                ManufacturingFamilyType::class,
                $manufacturingFamily
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('manufacturing.family.write.success', [], 'manufacturing')
                );

                return $this->redirectToRoute('manufacturing_family_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'actionPath' => $this->generateUrl('manufacturing_family_add'),
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'manufacturing_family_delete', methods: ['GET|DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_MANUFACTURING_FAMILY_ADMIN')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manufacturingFamily): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $manufacturingFamily['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('manufacturing.family.delete.success', [], 'manufacturing')
            );
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('manufacturing.family.delete.fail', [], 'manufacturing'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('manufacturing_family_home');
    }
}
