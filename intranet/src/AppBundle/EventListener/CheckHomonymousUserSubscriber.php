<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Client;
use AppBundle\Controller\Directory\PeopleController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckHomonymousUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $validHomonymousUser = $data['validHomonymousUser'] ?? null;

        if (isset($data['firstname'], $data['lastname']) && ('1' !== $validHomonymousUser)) {
            $existingPeople = $this->client->findBy(
                PeopleController::RESOURCE_URL,
                ['firstname' => $data['firstname'], 'lastname' => $data['lastname']]
            );

            foreach ($existingPeople->all() as $person) {
                $form->addError(new FormError(
                    $person['hidden'] ?
                        $this->translator->trans('directory.people.messages.error.valid_homonymous_user_hidden_error', ['%lastname' => $person['lastname'], '%firstname' => $person['firstname'], '%email' => $person['username']], 'directory') :
                        $this->translator->trans('directory.people.messages.error.valid_homonymous_user_error', ['%lastname' => $person['lastname'], '%firstname' => $person['firstname'], '%email' => $person['username']], 'directory')
                ));
            }

            if (!empty($existingPeople->all())) {
                $form->add('validHomonymousUser', CheckboxType::class, [
                    'label' => $this->translator->trans('directory.people.fields.confirm_homonymous_user', [], 'directory'),
                    'required' => true,
                    'mapped' => false,
                ]);
            }
        }
    }
}
