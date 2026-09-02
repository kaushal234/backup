<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Department;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use ApiBundle\Iri\Iri;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DepartmentDeleteType extends AbstractType
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
        $data = $builder->getData();

        $builder
            ->add('replacement', ChoiceType::class, [
                'label' => 'directory.department.fields.replacement',
                'required' => true,
                'choices' => $this->getReplacementOptions(Iri::id($data)),
                'placeholder' => 'directory.department.make_selection',
                'mapped' => false,
            ])
            ->add('confirm', CheckboxType::class, [
                'label' => 'directory.department.fields.confirm',
                'required' => true,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'directory.department.delete.confirm']),
                ],
                'mapped' => false,
            ])
        ;

        if (true === $data['used']) {
            $builder->get('replacement')->addModelTransformer(new IrisResourceToIdTransformer());
        } else {
            $builder->remove('replacement');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_department_delete';
    }

    private function getReplacementOptions($current): array
    {
        $apiDepartments = $this->dataProvider->findAll('departments', [], ['name']);

        $departments = [];
        foreach ($apiDepartments as $dpt) {
            if (Iri::id($dpt) !== $current) {
                $departments[$dpt['name']] = $dpt['@id'];
            }
        }

        return $departments;
    }
}
