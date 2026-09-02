<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use AppBundle\Form\Type\Directory\People\PeopleAdvancedChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', PeopleAdvancedChoiceType::class, [
                'query' => [
                    'peopleCurrentlyOrFutureEnabled' => [
                        'enableAt' => '1970-01-01',
                        'plannedEnableAt' => (new \DateTime('+4 days'))->format('Y-m-d'),
                    ],
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                ],
            ])
        ;
    }
}
