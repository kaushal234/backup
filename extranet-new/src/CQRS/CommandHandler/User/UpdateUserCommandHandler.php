<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\User;

use App\CQRS\Command\User\UpdateUserCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\User;
use App\Sdk\Utils\IriToId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class UpdateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(UpdateUserCommand $command): void
    {
        $phones = [];
        foreach ([
            'reception' => $command->updateUser->reception,
            'phone' => $command->updateUser->phone,
            'mobile' => $command->updateUser->mobile,
            'fax' => $command->updateUser->fax,
        ] as $type => $phone) {
            if (null === $phone) {
                continue;
            }
            $phones[] = [
                'type' => $type,
                'number' => $phone,
            ];
        }

        $this->client->update(User::class, ['resource_id' => IriToId::iriToId($command->updateUser->iri)], [
            '@id' => $command->updateUser->iri,
            'lastname' => $command->updateUser->lastname,
            'firstname' => $command->updateUser->firstname,
            'address' => [
                'street1' => $command->updateUser->street,
                'street2' => $command->updateUser->street2,
                'city' => $command->updateUser->city,
                'state' => $command->updateUser->state,
                'postalCode' => $command->updateUser->postalCode,
            ],
            'phones' => $phones,
            'extranetUserProfile' => [
                '@id' => $command->updateUser->profileIri,
                'department' => $command->updateUser->department,
                'division' => $command->updateUser->division,
                'jobTitle' => $command->updateUser->title,
                'language' => $command->updateUser->language,
                'country' => $command->updateUser->country->iri,
            ],
        ]);
    }
}
