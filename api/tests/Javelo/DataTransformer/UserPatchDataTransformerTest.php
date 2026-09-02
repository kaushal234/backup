<?php

declare(strict_types=1);

namespace App\Tests\Javelo\DataTransformer;

use App\Javelo\DataTransformer\UserPatchDataTransformer;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;

class UserPatchDataTransformerTest extends TestCase
{
    public function testTransform()
    {
        $javeloUser = $this->createMock(User::class);
        $javeloUser->title = 'Agent';
        $javeloUser->userName = 'jdoe';
        $javeloUser->familyName = 'Doe';
        $javeloUser->givenName = 'John';
        $javeloUser->locale = 'en-US';
        $javeloUser->active = true;
        $javeloUser->externalId = '12345';
        $javeloUser->intranetId = '12345';
        $javeloUser->department = 'Engineering';
        $javeloUser->managerUserName = 'manager_jdoe';
        $javeloUser->businessUnit = 'BU1';
        $javeloUser->region = 'North';
        $javeloUser->subdivision = 'Subdivision1';
        $javeloUser->division = 'Division1';
        $javeloUser->gender = 'male';
        $javeloUser->contractType = 'Etudiant';
        $javeloUser->method('getLastExitDate')->willReturn('2022-12-05');
        $javeloUser->position = 'standardiste';
        $javeloUser->workingTime = '100';

        $transformer = new UserPatchDataTransformer();
        $result = $transformer->transform($javeloUser);

        $expected = [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                ['op' => 'Replace', 'path' => 'title', 'value' => 'Agent'],
                ['op' => 'Replace', 'path' => 'userName', 'value' => 'jdoe'],
                ['op' => 'Replace', 'path' => 'name.familyName', 'value' => 'Doe'],
                ['op' => 'Replace', 'path' => 'name.givenName', 'value' => 'John'],
                ['op' => 'Replace', 'path' => 'locale', 'value' => 'en-US'],
                ['op' => 'Replace', 'path' => 'active', 'value' => true],
                ['op' => 'Replace', 'path' => 'externalId', 'value' => '12345'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:department', 'value' => 'Engineering'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:intranetId', 'value' => '12345'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:managerUserName', 'value' => 'manager_jdoe'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:businessUnit', 'value' => 'BU1'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:region', 'value' => 'North'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:subdivision', 'value' => 'Subdivision1'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:division', 'value' => 'Division1'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:gender', 'value' => 'male'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:contractType', 'value' => 'Etudiant'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:lastExitDate', 'value' => '2022-12-05'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:position', 'value' => 'standardiste'],
                ['op' => 'Replace', 'path' => 'urn:ietf:params:scim:schemas:extension:javelo:2.0:User:workingTime', 'value' => '100'],
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithInvalidValue()
    {
        $transformer = new UserPatchDataTransformer();

        $this->assertNull($transformer->transform(new \stdClass()));
        $this->assertNull($transformer->transform(null));
    }
}
