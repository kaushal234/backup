<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class VendorWarrantyClaimTypeChoiceType extends AbstractType
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
            'choice_translation_domain' => false,
            'extra_choices' => [],
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    'purchasing/vendor_warranty_claim_types',
                    [],
                    ['id']
                );

                $choices = [];
                foreach ($collection as $vendorWarrantyClaimType) {
                    $value = \sprintf('%s (%s)', $vendorWarrantyClaimType['description'], $vendorWarrantyClaimType['name']);
                    $choices[$value] = $vendorWarrantyClaimType[$options['key']];
                }

                return $choices;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
