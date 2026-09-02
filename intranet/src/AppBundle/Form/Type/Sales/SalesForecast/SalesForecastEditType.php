<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Quote\QuoteAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class SalesForecastEditType extends AbstractType
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $percentageChoices = range(0, 100, 5);

        $isMOO = $this->authorizationChecker->isGranted('MOO_SFR');

        $statusChoices = [
            'BUDGET' => 'BUDGET',
            'DELAYED' => 'DELAYED',
            'IN PROGRESS' => 'IN_PROGRESS',
        ];

        if ($isMOO || $this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_FORCE_STATUS')) {
            $statusChoices = array_merge($statusChoices, [
                SalesForecastsCloseType::CANCELLED => SalesForecastsCloseType::CANCELLED,
                SalesForecastsCloseType::LOST => SalesForecastsCloseType::LOST,
                SalesForecastsCloseType::ORDERED => SalesForecastsCloseType::ORDERED,
                str_replace('_', ' ', SalesForecastsCloseType::ORDER_CANCELLED) => SalesForecastsCloseType::ORDER_CANCELLED,
            ]);
        }

        $sfr = $builder->getData();

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.status',
                'required' => false,
                'choices' => $statusChoices,
                'choice_translation_domain' => false,
            ])
            ->add('synchronized', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.sfr_edit_linked',
                'required' => false,
                'mapped' => false,
            ])
            ->add('asm', ASMAutocompleteChoiceType::class, [
                'label' => 'sales_forecasts.fields.asm',
            ])
            ->add('product', ProductChoiceType::class, [
                'label' => 'sales_forecasts.fields.product',
                'attr' => ['class' => 'unsynchronized'],
                'extra_choices' => ['' => null],
                'constraints' => [
                    new NotNull(),
                ],
                'data' => str_contains($sfr['product']['name'], 'OBSOLETE') ? null : $sfr['product']['@id'],
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('buyer', CustomerAutocompleteChoiceType::class, [
                'template' => '{{ name }} - {{ status }}',
                'label' => 'fields.buyer',
                'translation_domain' => 'messages',
            ])
            ->add('endUser', CustomerAutocompleteChoiceType::class, [
                'template' => '{{ name }} - {{ status }}',
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
            ])
            ->add('thirdParty', CustomerAutocompleteChoiceType::class, [
                'template' => '{{ name }} - {{ status }}',
                'required' => false,
                'label' => 'sales_forecasts.fields.third_party',
            ])
            ->add('equoteId', TextType::class, [
                'label' => 'sales_forecasts.fields.equote',
                'required' => false,
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('tier', EmissionRatingChoiceType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'fields.quantity',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('margin', NumberType::class, [
                'label' => 'sales_forecasts.fields.margin',
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('price', IntegerType::class, [
                'label' => 'sales_forecasts.fields.price',
                'attr' => ['class' => 'unsynchronized'],
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'address.fields.country',
                'translation_domain' => 'messages',
            ])
            ->add('airport', AirportChoiceType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('customerSuccessPercentage', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.customer_purchase_percentage',
                'choices' => array_combine($percentageChoices, $percentageChoices),
            ])
            ->add('successPercentage', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.alvest_success_percentage',
                'choices' => array_combine($percentageChoices, $percentageChoices),
            ])
            ->add('estimatedSaleDate', DatePickerType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'format' => 'MM/yyyy',
                'defaultDate' => (new \DateTime())->modify('+2 years'),
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => '-5 years',
                ],
                'required' => false,
                'label' => 'sales_forecasts.fields.estimated_sale_date',
                'help' => \sprintf('MM/YYYY  -  First available date : %s', (new \DateTime())->modify('+2 years')->format('m/Y')), // TODO: Dynamic format in helper when Intl is back
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'fields.comment',
                'translation_domain' => 'messages',
                'attr' => [
                    'style' => 'resize:vertical',
                ],
                'mapped' => false,
            ])
            ->add('notificationRestricted', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.notification_restricted',
                'required' => false,
                'mapped' => false,
            ])
            ->add('notifyPackage', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.notify_package',
                'data' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('quote', QuoteAutocompleteChoiceType::class, [
                'label' => 'sales_forecasts.fields.ln_quote',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        foreach ($builder->all() as $key => $form) {
            if (!\in_array($key, $options['authorizedFields'], true) && 'submit' !== $key) {
                $field = $builder->get($key);
                $field->setDisabled(true);
            }
        }

        if (null !== $options['countLinkedSFR'] && $options['countLinkedSFR'] < 1) {
            $builder->remove('synchronized');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
            'authorizedFields' => null,
            'countLinkedSFR' => null,
        ]);
    }
}
