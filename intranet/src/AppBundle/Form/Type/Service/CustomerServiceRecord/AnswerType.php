<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\Form\Type\Service\CustomerServiceRecord\Survey\BuilderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AnswerType extends AbstractType
{
    public const ASPECT = 0;
    public const CONFORMITY = 1;
    public const OPERATIONAL = 2;
    public const SHIPPING = 3;
    public const IS_LINK_WORKING = 4;

    public function __construct(
        private readonly BuilderFactory $builderFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderFactory = $this->builderFactory;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($builderFactory) {
            $data = $event->getData();
            $builder = $builderFactory->getBuilder($data);

            if (!$builder) {
                return;
            }

            $builder->addForm($event->getForm(), $data);
        });

        $builder->addModelTransformer(new CallbackTransformer(
            static function ($value) use ($builderFactory) {
                if (!$value) {
                    return $value;
                }

                $builder = $builderFactory->getBuilder($value);

                if ($builder && $value['answer']) {
                    $value = $builder->transform($value);
                }

                return $value;
            },
            static function ($value) use ($builderFactory) {
                if (!$value) {
                    return $value;
                }

                $builder = $builderFactory->getBuilder($value);

                if ($builder) {
                    $value = $builder->reverse($value);
                }

                return $value;
            }
        ));
    }
}
