<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Parts\PartsNumberAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\FirstArticleQualificationAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\NonConformity\NonConformityAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrabEditType extends AbstractType
{
    private readonly string $uploadDir;
    private readonly Security $security;

    public function __construct(string $uploadDir, Security $security)
    {
        $this->uploadDir = $uploadDir;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $crab = $builder->getData();
        $file = isset($crab['mainFile']) && null !== $crab['mainFile'] && file_exists($filePath = \sprintf('%s/%s', $this->uploadDir, $crab['mainFile']['filePath'])) ? new File($filePath) : null;

        $builder
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class)
            ->add('department', CrabDepartmentChoiceType::class, [
                'label' => 'crab.fields.department',
                'required' => false,
                'translation_domain' => 'crab',
            ])
            ->add('code', CodeAutocompleteChoiceType::class, [
                'required' => true,
            ])
            ->add('nonConformity', NonConformityAutocompleteChoiceType::class, [
                'required' => false,
            ])
            ->add('firstArticleQualification', FirstArticleQualificationAutocompleteChoiceType::class, [
                'required' => false,
            ])
            ->add('eapId', IntegerType::class, [
                'required' => false,
                'label' => 'crab.fields.eap_id',
                'translation_domain' => 'crab',
            ])
            ->add('category', CategoryChoiceType::class)
            ->add('description', TextareaType::class, [
                'label' => 'crab.fields.description',
                'translation_domain' => 'crab',
            ])
            ->add('mainFile', FileType::class, [
                'translation_domain' => 'file_type',
                'required' => false,
                'data' => $file,
                'label' => 'file_type.file_upload',
                'help' => $crab['mainFile']['filePath'] ?? null,
            ])
            ->add('part', PartsNumberAutocompleteChoiceType::class, [
                'label' => 'fields.part_number',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'crab.button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'crab',
            ])
        ;

        if ('TO-FIX' !== $crab['status'] && $this->security->isGranted('FEATURE_CRAB_EDIT_FIX')) {
            $builder->add(
                'fixingComments', TextareaType::class, [
                    'label' => 'crab.fields.fixing_comments',
                    'translation_domain' => 'crab',
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
