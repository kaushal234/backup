<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPeopleLifecycleStatusFilterType extends AbstractFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler($this->createHandler());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => $this->getChoices(),
                'choice_translation_domain' => 'directory',
                'multiple' => true,
            ],
        ]);
    }

    /**
     * The handler mapping the selected status to the `disabled` API filter.
     */
    abstract protected function createHandler(): FilterHandlerInterface;

    /**
     * The selectable statuses as a translation key => status value map.
     *
     * @return array<string, string>
     */
    abstract protected function getChoices(): array;
}
