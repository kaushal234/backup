<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class CustomerChoiceType extends AbstractType
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(DataProvider $dataProvider, TranslatorInterface $translator)
    {
        $this->dataProvider = $dataProvider;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'key' => '@id',
            'filters' => [],
            'choice_translation_domain' => false,
            'show_only_with_crt' => false,
            'extra_choices' => [],
            'choices' => function (Options $options): array {
                $filters = $options['filters'];
                if (false !== $options['show_only_with_crt']) {
                    $filters['normalization_groups_override'] = ['customer_list', 'customer_crt'];
                }
                $collection = $this->dataProvider->findAll(
                    'sales/customers',
                    $filters,
                    ['name']
                );

                $choices = [];

                foreach ($collection as $customer) {
                    if (false !== $options['show_only_with_crt'] && 0 === \count($customer['crt'])) {
                        continue;
                    }
                    $value = $customer['name'];
                    if ('APPROVED' !== $customer['status']) {
                        $value .= " (status: {$customer['status']})";
                    }
                    $choices[$value] = $customer[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + [
            'hidden' => 0,
            'normalization_groups_override' => ['customer_list'],
            'pagination' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'app_sales_customers_choice';
    }
}
