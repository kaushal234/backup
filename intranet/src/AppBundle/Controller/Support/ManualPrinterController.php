<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\Printer\PrinterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/printers', defaults: ['alvest_module' => 'PUBS', 'breadcrumb_label' => 'menu.manual_printer.title', 'moduleDomain' => 'printer'])]
class ManualPrinterController extends AbstractController
{
    final public const RESOURCE_URL = 'support/manual_printers';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'printer_home', methods: ['GET'])]
    #[Template('support/printer/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $manualPrinters)
    {
        return ['printers' => $manualPrinters];
    }

    #[Route(path: '/{id}/show', name: 'printer_show', methods: ['GET'])]
    #[Template('support/printer/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manualPrinter)
    {
        return ['printer' => $manualPrinter];
    }

    #[Route(path: '/add', name: 'printer_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'printer_edit', methods: ['GET', 'POST'])]
    #[Template('support/printer/write.html.twig')]
    #[IsGranted('FEATURE_PRINTER_WRITE')]
    public function write(Request $request, FormFactoryInterface $formFactory, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $manualPrinter = null)
    {
        $form = $formFactory
            ->createNamed(
                'printer_form',
                PrinterType::class,
                $manualPrinter
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('support.printer.edit.success', [], 'support')
                );
                if (null === $manualPrinter) {
                    return $this->redirectToRoute('printer_home');
                }

                return $this->redirectToRoute('printer_show', ['id' => $manualPrinter['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'printer_delete', methods: ['GET', 'DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_PRINTER_WRITE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manualPrinter): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $manualPrinter->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('support.printer.delete.success', [], 'support')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('support.printer.delete.error', [], 'support')
            );

            return $this->redirectToRoute('printer_home', ['id' => $manualPrinter->getIriId()]);
        }

        return $this->redirectToRoute('printer_home');
    }
}
