<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\Premise\PremiseTagType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/premises/type', defaults: ['alvest_module' => 'DIR'])]
class PremiseTagController extends AbstractController
{
    final public const RESOURCE_URL = 'premise_tags';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'premise_tag_home', methods: ['GET'])]
    #[Template('directory/premise/premise_tag/home.html.twig')]
    #[IsGranted('FEATURE_PREMISE_WRITE')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => 'tags', 'filters' => ['resourceType' => 'PremiseTag']])] HydraCollection $premiseTags)
    {
        return ['premiseTags' => $premiseTags];
    }

    #[Route(path: '/add', name: 'premise_tag_add', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'premise_tag_edit', methods: ['GET|POST'])]
    #[Template('directory/premise/premise_tag/write.html.twig')]
    #[IsGranted('FEATURE_PREMISE_WRITE')]
    public function write(Request $request, FormFactoryInterface $formFactory, #[ApiValueResolverAttribute(parameters: ['resource' => 'tags'])] ?ApiData $premiseTag = null)
    {
        $form = $formFactory
            ->createNamed(
                'premise_tag_form',
                PremiseTagType::class,
                $premiseTag
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.premise.type.edit.success', [], 'directory')
                );

                return $this->redirectToRoute('premise_tag_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'premise_tag_delete', methods: ['GET|DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_PREMISE_WRITE')]
    public function delete(#[ApiValueResolverAttribute] ApiData $premiseTag): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $premiseTag->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('directory.premise.type.delete.success', [], 'directory')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('directory.premise.type.delete.error', [], 'directory')
            );

            return $this->redirectToRoute('premise_tag_home', ['id' => $premiseTag->getIriId()]);
        }

        return $this->redirectToRoute('premise_tag_home');
    }
}
