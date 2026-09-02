<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\User;

use App\CQRS\Command\User\UpdateUserCommand;
use App\CQRS\CommandHandler\User\UpdateUserCommandHandler;
use App\DataTransferObject\Country;
use App\DataTransferObject\User\UpdateUser;
use App\Sdk\Client;
use App\Sdk\Resource\User;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class UpdateUserCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('update')->with(User::class, ['resource_id' => 1], [
            '@id' => '/people/1',
            'lastname' => 'foo',
            'firstname' => 'bar',
            'address' => [
                'street1' => 'street',
                'street2' => 'street2',
                'city' => 'city',
                'state' => 'state',
                'postalCode' => '37100',
            ],
            'phones' => [
                [
                    'type' => 'reception',
                    'number' => '+33 6 01 02 03 04',
                ],
                [
                    'type' => 'phone',
                    'number' => '+33 6 01 02 03 04',
                ],
                [
                    'type' => 'mobile',
                    'number' => '+33 6 01 02 03 04',
                ],
                [
                    'type' => 'fax',
                    'number' => '+33 2 47 48 49 50',
                ],
            ],
            'extranetUserProfile' => [
                '@id' => '/profile/1',
                'department' => 'indre et loire',
                'division' => 'ligue 1',
                'jobTitle' => 'mecanicien',
                'language' => 'fr',
                'country' => '/foo/1',
            ],
        ]);

        $country = new Country();
        $country->iri = '/foo/1';

        $user = new UpdateUser();
        $user->iri = '/people/1';
        $user->profileIri = '/profile/1';
        $user->lastname = 'foo';
        $user->firstname = 'bar';
        $user->division = 'ligue 1';
        $user->title = 'mecanicien';
        $user->department = 'indre et loire';
        $user->language = 'fr';
        $user->street = 'street';
        $user->street2 = 'street2';
        $user->state = 'state';
        $user->postalCode = '37100';
        $user->city = 'city';
        $user->country = $country;
        $user->phone = '+33 6 01 02 03 04';
        $user->mobile = '+33 6 01 02 03 04';
        $user->reception = '+33 6 01 02 03 04';
        $user->fax = '+33 2 47 48 49 50';

        $command = new UpdateUserCommand($user);
        $handler = new UpdateUserCommandHandler($client);
        $handler->__invoke($command);
    }
}
