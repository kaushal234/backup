<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhoneType extends AbstractType
{
    final public const TYPE_RECEPTION = 'reception';
    final public const TYPE_PHONE = 'phone';
    final public const TYPE_MOBILE = 'mobile';
    final public const TYPE_MOBILE_ALTERNATE = 'mobile_alternate';
    final public const TYPE_FAX = 'fax';
    final public const TYPE_HOME = 'home';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'phone.fields.type',
                'choices' => self::getTypeOptions(),
                'placeholder' => 'phone.make_selection',
                'constraints' => [new NotBlank()],
            ])
            ->add('number', TextType::class, [
                'label' => 'phone.fields.number',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_phone';
    }

    /**
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_RECEPTION => self::TYPE_RECEPTION,
            self::TYPE_PHONE => self::TYPE_PHONE,
            self::TYPE_MOBILE => self::TYPE_MOBILE,
            self::TYPE_MOBILE.' (alternate)' => self::TYPE_MOBILE_ALTERNATE,
            self::TYPE_FAX => self::TYPE_FAX,
            self::TYPE_HOME => self::TYPE_HOME,
        ];
    }
}
