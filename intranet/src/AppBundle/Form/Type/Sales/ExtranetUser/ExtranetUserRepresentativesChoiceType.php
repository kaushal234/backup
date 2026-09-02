<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ExtranetUserRepresentativesChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'filters' => [],
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'sales/extranet_user_acls',
                        $options['filters']
                    );

                    $choices = [];
                    foreach ($collection as $acl) {
                        $salesRepValue = \sprintf('%s, %s, %s', $acl['crt']['salesRepresentative']['lastname'], $acl['crt']['salesRepresentative']['firstname'], $acl['crt']['salesRepresentative']['email']);
                        $partsRepValue = \sprintf('%s, %s, %s', $acl['crt']['partsRepresentative']['lastname'], $acl['crt']['partsRepresentative']['firstname'], $acl['crt']['partsRepresentative']['email']);
                        $serviceRepValue = \sprintf('%s, %s, %s', $acl['crt']['serviceRepresentative']['lastname'], $acl['crt']['serviceRepresentative']['firstname'], $acl['crt']['serviceRepresentative']['email']);
                        $choices[$salesRepValue] = $acl['crt']['salesRepresentative']['@id'];
                        $choices[$partsRepValue] = $acl['crt']['partsRepresentative']['@id'];
                        $choices[$serviceRepValue] = $acl['crt']['serviceRepresentative']['@id'];
                    }

                    return $choices;
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_xu_rep_for_email';
    }
}
