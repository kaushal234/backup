<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerWatchListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('watchListReason', TextareaType::class, [
                'label' => 'customers.fields.watch_list_reason',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('put_on_watchlist', SubmitType::class, [
                'label' => 'customers.fields.watch_list_put',
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->add('remove_from_watchlist', SubmitType::class, [
                'label' => 'customers.fields.watch_list_remove',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) {
                $form = $event->getForm();

                $customer = $event->getData();

                if (true === $customer['watchList']) {
                    $form->remove('watchListReason');
                }
                $form->remove(true === $customer['watchList'] ? 'put_on_watchlist' : 'remove_from_watchlist');
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_customers',
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_customer';
    }
}
