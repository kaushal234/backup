<?php

declare(strict_types=1);

namespace App\Twig\PurchaseOrder;

use App\Form\PurchaseOrder\GenerateLabelsType;
use App\Form\PurchaseOrder\PopulateColumnType;
use App\Sdk\Resource\PurchaseOrder;
use App\Sdk\Resource\PurchaseOrderLine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_purchase_order_line_edit_form', $this->getLineForm(...)),
            new TwigFunction('get_purchase_order_edit_form', $this->getOrderLinesForm(...)),
            new TwigFunction('get_purchase_order_message_form', $this->getOrderForm(...)),
            new TwigFunction('get_populate_column_select', $this->getPopulateColumnSelect(...)),
            new TwigFunction('create_purchase_order_generate_labels_form', $this->createGenerateLabelsForm(...)),
        ];
    }

    public function getPopulateColumnSelect(): FormView
    {
        return $this->factory->createNamedBuilder(PopulateColumnType::createName(), PopulateColumnType::class)->getForm()->createView();
    }

    public function getLineForm(FormView $form, PurchaseOrderLine $purchaseOrderLine): ?FormView
    {
        foreach ($form->children['editLines'] as $lineForm) {
            if ($lineForm->vars['value']->lineIdentifier === $purchaseOrderLine->lineIdentifier && $lineForm->vars['value']->sequence === $purchaseOrderLine->sequence) {
                return $lineForm;
            }
        }

        return null;
    }

    public function getOrderLinesForm(FormView $form, PurchaseOrder $purchaseOrder, PurchaseOrderLine $line): ?FormView
    {
        $orderForm = $this->getOrderForm($form, $purchaseOrder);
        if ($orderForm && $orderForm->vars['value']->iri === $purchaseOrder->iri) {
            return $this->getLineForm($orderForm, $line);
        }

        return null;
    }

    public function getOrderForm(FormView $form, PurchaseOrder $purchaseOrder): ?FormView
    {
        foreach ($form->children['editPurchaseOrders'] as $orderForm) {
            if ($orderForm->vars['value']->iri === $purchaseOrder->iri) {
                return $orderForm;
            }
        }

        return null;
    }

    public function createGenerateLabelsForm(PurchaseOrder $order): FormView
    {
        $url = $this->urlGenerator->generate('purchase-order:generate_labels', ['id' => $order->id, 'erp' => $order->erp]);
        $formLabels = $this->factory->createNamedBuilder(GenerateLabelsType::createName($order), GenerateLabelsType::class, $order, [
            'attr' => [
                'id' => 'confirm_label',
            ],
        ]);

        $formLabels->setAction($url);

        return $formLabels->getForm()->createView();
    }
}
