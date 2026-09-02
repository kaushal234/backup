<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Premise;

use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Mis\SupportTeam\SupportTeamChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PremiseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $premise = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'required' => true,
                'constraints' => [new Length(max: 10), new NotBlank()],
            ])
            ->add('description', TextType::class, [
                'label' => 'fields.description',
                'required' => true,
            ])
            ->add('tags', PremiseTagChoiceType::class, [
                'required' => true,
                'multiple' => true,
            ])
            ->add('address', AddressType::class, [
                'label' => 'address.name',
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'support.iata.fields.latitude',
                'translation_domain' => 'support',
                'required' => true,
                'html5' => true,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'support.iata.fields.longitude',
                'translation_domain' => 'support',
                'required' => true,
                'html5' => true,
            ])
            ->add('airport', AirportChoiceType::class, [
                'label' => 'fields.closest_airport',
                'required' => true,
                'translation_domain' => 'messages',
            ])
            ->add('supportTeam', SupportTeamChoiceType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if (null !== $builder->getData() && 0 === $builder->getData()['count']) {
            $builder->add('archived', CheckboxType::class, [
                'label' => 'contacts.fields.archived',
                'required' => false,
                'translation_domain' => 'contacts',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'messages']);
    }
}
