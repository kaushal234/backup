<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use AppBundle\Form\Type\SimpleFileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerFileType extends SimpleFileType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('subDivision', SubDivisionChoiceType::class, [
            'required' => true,
            'label' => 'directory.sub_division.name',
            'translation_domain' => 'directory',
        ]);

        $builder->add('isContract', CheckboxType::class, [
            'required' => false,
            'label' => 'file_type.is_contract_file',
            'help' => 'file_type.is_contract_file_help',
        ]);
    }
}
