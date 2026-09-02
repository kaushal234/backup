<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class AclChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acls', ChoiceType::class, [
                'choices' => $this->getAcls($options['user'], $options['location'] ?? null),
                'choice_translation_domain' => false,
                'label' => 'directory.import_acl.select_acls',
                'translation_domain' => 'directory',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['user']);
        $resolver->setDefined(['location']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people_acl_choice';
    }

    private function getAcls($user, $location = null): array
    {
        $res = $this->dataProvider->findAll('acls', ['user' => $user], ['group.name']);

        $acls = [];
        foreach ($res as $acl) {
            $key = $acl['group']['name'];

            if (!empty($location)) {
                $locations = $this->dataProvider->findAll('locations');
                foreach ($locations as $loc) {
                    if ($loc['@id'] === $location) {
                        $key .= " - {$loc['name']} ";
                        break;
                    }
                }
            } else {
                $key .= empty($acl['location']['name']) ? '' : " - {$acl['location']['name']}";
            }

            $acls[$key] = $acl['@id'];
        }

        return $acls;
    }
}
