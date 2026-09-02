<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Manager\Directory\TeamMemberManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechniciansChoiceType extends AbstractType
{
    public function __construct(
        private readonly TeamMemberManager $teamMemberManager,
    ) {
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
        $collection = $this->teamMemberManager->getFlatTechnicians();

        $resolver->setDefaults([
            'filters' => [],
            'choice_translation_domain' => false,
            'choices' => static function (Options $options) use ($collection): array {
                $choices = [];
                foreach ($collection as $user) {
                    $value = \sprintf('%s, %s', $user['lastname'], $user['firstname']);
                    $choices[$value] = $user['@id'];
                }

                return $choices;
            },
        ]);

        $resolver->define('treeTechnicians')->default($this->teamMemberManager->createTree($collection));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['treeTechnicians'] = $options['treeTechnicians'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people_choice';
    }
}
