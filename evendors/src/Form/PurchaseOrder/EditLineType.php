<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use App\DataTransferObject\PurchaseOrder\EditPurchaseOrderLine;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditLineType extends AbstractType
{
    private const INVALID_DELIVERY_DATE = 'purchase_order.line.edit.confirmed_delivery_date.invalid';
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $error = $this->translator->trans(self::INVALID_DELIVERY_DATE);
        $builder
            ->add('confirmedSupplierDate', DateType::class, [
                'input' => 'datetime_immutable',
                'required' => false,
                'label' => false,
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'js-delivery-date-datepicker form-control confirmed-delivery-date-input',
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($error): void {
                $data = $event->getData();
                $form = $event->getForm();
                $editPurchaseOrderLine = $form->getData();
                if ($editPurchaseOrderLine->isConfirmable && $editPurchaseOrderLine->isUpdatedConfirmedDate($data['confirmedSupplierDate'])) {
                    $options = $form->get('confirmedSupplierDate')->getConfig()->getOptions();
                    $options['constraints'] = [
                        new NotBlank(message: $error),
                        new GreaterThan(new DateTimeImmutable('yesterday'), null, $error),
                    ];
                    $editPurchaseOrderLine->update = true;
                    $form->add('confirmedSupplierDate', DateType::class, $options);
                }
                $event->setData($data);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EditPurchaseOrderLine::class,
        ]);
    }
}
