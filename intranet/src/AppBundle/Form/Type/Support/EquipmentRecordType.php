<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EquipmentRecordType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estimatedGreenTagDate', DatePickerType::class, [
                'attr' => ['style' => 'min-width: 120px', 'class' => 'datepicker'],
                'required' => false,
            ])
            ->add('yellowTagDate', DatePickerType::class, [
                'attr' => ['style' => 'min-width: 120px', 'class' => 'datepicker'],
                'required' => false,
            ])
            ->add('newGreenTagDate', CheckboxType::class, [
                'required' => false,
            ])
            ->add('dateShipped', DatePickerType::class, [
                'attr' => ['style' => 'min-width: 120px', 'class' => 'datepicker'],
                'required' => false,
            ])
            ->add('odpComment', TextType::class, [
                'required' => false,
                'attr' => ['style' => 'min-width: 300px'],
            ])
        ;

        if (!$this->authorizationChecker->isGranted('FEATURE_ODP_EDIT_SUPPORT')) {
            $builder->get('dateShipped')->setDisabled(true);
            $builder->get('estimatedGreenTagDate')->setDisabled(true);
            $builder->get('odpComment')->setDisabled(true);
        }
        if (!$this->authorizationChecker->isGranted('FEATURE_ODP_EDIT_QUALITY')) {
            $builder->get('yellowTagDate')->setDisabled(true);
            $builder->get('newGreenTagDate')->setDisabled(true);
        }
    }
}
