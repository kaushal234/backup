<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use ApiBundle\Client;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesForecastTransferType extends AbstractType
{
    private readonly Client $client;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->client->get('/me');

        $defaultLocation = $user['businessUnit']['location']['@id'] ?? null;

        $builder
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'data' => $defaultLocation,
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('asmSource', ASMAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'sales_forecasts.fields.asm',
            ])
            ->add('country', CountryChoiceType::class, [
                'required' => false,
                'label' => 'address.fields.country',
                'translation_domain' => 'messages',
            ])
            ->add('buyer', CustomerAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
            ])
            ->add('endUser', CustomerAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
            ])
            ->add('asmTarget', ASMAutocompleteChoiceType::class, [
                'label' => 'sales_forecasts.fields.transfer_target',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-info',
                    'onclick' => \sprintf("return confirm('%s')", $this->translator->trans('sales_forecasts.messages.transfer_confirm', [], 'sales_forecasts')),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
        ]);
    }
}
