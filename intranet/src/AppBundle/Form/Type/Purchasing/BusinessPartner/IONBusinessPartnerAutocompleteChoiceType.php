<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\BusinessPartner;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IONBusinessPartnerAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'id_key' => '[code]',
                'uri' => 'ion/business_partners',
                'query' => [
                    'role' => 'supplier',
                ],
                'template' => '{{ code }} {{ name }}',
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add base URI to the init value to retrieve the complete value from API.
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event): void {
                $data = $event->getData();
                if (null === $data) {
                    return;
                }
                $event->setData('ion/business_partners/'.$data);
            }
        );

        // Strip the URI prefix added by PRE_SET_DATA so the raw code reaches the model.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            static function (FormEvent $event): void {
                $data = $event->getData();
                if (\is_string($data) && str_starts_with($data, 'ion/business_partners/')) {
                    $event->setData(mb_substr($data, mb_strlen('ion/business_partners/')));
                }
            }
        );
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
