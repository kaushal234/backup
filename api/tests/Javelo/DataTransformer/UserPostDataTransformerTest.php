<?php

declare(strict_types=1);

namespace App\Tests\Javelo\DataTransformer;

use App\Javelo\DataTransformer\UserPostDataTransformer;
use App\Javelo\Resources\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserPostDataTransformerTest extends KernelTestCase
{
    private UserPostDataTransformer $transformer;

    protected function setUp(): void
    {
        self::bootKernel();
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);
        $this->transformer = new UserPostDataTransformer($normalizer);
    }

    public function testTransform(): void
    {
        $user = new User();
        $user->id = '12345';
        $user->userName = 'jdoe';
        $user->givenName = 'John';
        $user->familyName = 'Doe';
        $user->managerUserName = 'asmith';
        $user->intranetId = '98765';
        $user->department = 'MIS';

        $result = $this->transformer->transform($user);
        $expected = [
            'urn:ietf:params:scim:schemas:extension:javelo:2.0:User' => [
                'lastExitDate' => null,
                'managerUserName' => 'asmith',
                'intranetId' => '98765',
                'businessUnit' => null,
                'region' => null,
                'subdivision' => null,
                'division' => null,
                'gender' => null,
                'contractType' => null,
                'position' => null,
                'workingTime' => null,
            ],
            'id' => '12345',
            'externalId' => null,
            'userName' => 'jdoe',
            'name' => [
                'familyName' => 'Doe',
                'givenName' => 'John',
            ],
            'active' => false,
            'locale' => null,
            'title' => null,
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User' => [
                'department' => 'MIS',
            ],
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testTransformWithInvalidValue()
    {
        $this->assertNull($this->transformer->transform(new \stdClass()));
        $this->assertNull($this->transformer->transform(null));
    }
}
