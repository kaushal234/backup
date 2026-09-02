<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use ApiBundle\Client;
use AppBundle\EventListener\CheckHomonymousUserSubscriber;
use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddPeopleType extends AbstractType
{
    protected $fieldsRestricted = [
        'password',
        'hidden',
        'disabled',
        'erpLogin',
        'windowsLogin',
        'token',
        'email',
        'username',
    ];

    public function __construct(private readonly Client $client, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->fieldsRestricted as $name) {
            $builder->remove($name);
        }
        // Only for new People, change for auto-update-user
        $builder->add('enableAt', DatePickerType::class, [
            'label' => 'directory.people.fields.enable_at',
            'help' => 'MM/DD/YYYY : IMPORTANT! CONNECTION ACCESSIBLE FROM THIS DATE',
            'restrictions' => [
                'minDateStr' => '-1 day',
            ],
            'required' => true,
        ]);
        $builder->addEventSubscriber(new CheckHomonymousUserSubscriber($this->client, $this->translator));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return PeopleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_add_people';
    }
}
