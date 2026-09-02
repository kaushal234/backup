<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Custom;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\ToolStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ToolStatusChoice.
 */
class ToolStatusChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => ToolStatus::getStatuses(),
            'translation_domain' => 'calibration_tool',
        ]);
    }

    public function getParent(): string
    {
        return SelectFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_tool_status_choice';
    }
}
