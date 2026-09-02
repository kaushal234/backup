<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Resources;

use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class UserSerializationTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $propertyAccessor = new PropertyAccessor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyInfo = new PropertyInfoExtractor([], [$phpDocExtractor]);

        $normalizers = [new ObjectNormalizer($classMetadataFactory, null, $propertyAccessor, $propertyInfo)];
        $encoders = [new JsonEncoder()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testSerialization(): void
    {
        $user = new User();
        $user->id = '123';
        $user->externalId = 'ext-456';
        $user->userName = 'john_doe';
        $user->familyName = 'Doe';
        $user->givenName = 'John';
        $user->active = true;
        $user->locale = 'en_US';
        $user->title = 'Mr';
        $user->department = 'Engineering';
        $user->managerUserName = 'manager_jane';
        $user->intranetId = 'intranet-789';
        $user->businessUnit = 'BU1';
        $user->region = 'North';
        $user->subdivision = 'Subdivision1';
        $user->division = 'Division1';
        $user->gender = 'Mr';
        $user->contractType = 'Etudiant';
        $user->setLastExitDate('2025-12-25');
        $user->position = 'Développeur';
        $user->workingTime = '72';

        $json = $this->serializer->serialize($user, 'json');
        $expectedJson = json_encode([
            'id' => '123',
            'externalId' => 'ext-456',
            'userName' => 'john_doe',
            'name' => [
                'familyName' => 'Doe',
                'givenName' => 'John',
            ],
            'active' => true,
            'locale' => 'en_US',
            'title' => 'Mr',
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User' => [
                'department' => 'Engineering',
            ],
            'urn:ietf:params:scim:schemas:extension:javelo:2.0:User' => [
                'managerUserName' => 'manager_jane',
                'intranetId' => 'intranet-789',
                'businessUnit' => 'BU1',
                'region' => 'North',
                'subdivision' => 'Subdivision1',
                'division' => 'Division1',
                'gender' => 'Mr',
                'contractType' => 'Etudiant',
                'lastExitDate' => '2025-12-25',
                'position' => 'Développeur',
                'workingTime' => '72',
            ],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }

    public function testDeserialization(): void
    {
        $json = json_encode([
            'id' => '123',
            'externalId' => 'ext-456',
            'userName' => 'john_doe',
            'name' => [
                'familyName' => 'Doe',
                'givenName' => 'John',
            ],
            'active' => true,
            'locale' => 'en_US',
            'title' => 'Mr',
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User' => [
                'department' => 'Engineering',
            ],
            'urn:ietf:params:scim:schemas:extension:javelo:2.0:User' => [
                'managerUserName' => 'manager_jane',
                'intranetId' => 'intranet-789',
                'businessUnit' => 'BU1',
                'region' => 'North',
                'subdivision' => 'Subdivision1',
                'division' => 'Division1',
                'gender' => 'male',
                'workingTime' => '72',
                'position' => 'Développeur',
                'lastExitDate' => '2025-12-25',
                'contractType' => 'Etudiant',
            ],
        ]);

        /** @var User $user */
        $user = $this->serializer->deserialize($json, User::class, 'json');

        $this->assertSame('123', $user->id);
        $this->assertSame('ext-456', $user->externalId);
        $this->assertSame('john_doe', $user->userName);
        $this->assertSame('Doe', $user->familyName);
        $this->assertSame('John', $user->givenName);
        $this->assertTrue($user->active);
        $this->assertSame('en_US', $user->locale);
        $this->assertSame('Mr', $user->title);
        $this->assertSame('Engineering', $user->department);
        $this->assertSame('manager_jane', $user->managerUserName);
        $this->assertSame('intranet-789', $user->intranetId);
        $this->assertSame('BU1', $user->businessUnit);
        $this->assertSame('North', $user->region);
        $this->assertSame('Subdivision1', $user->subdivision);
        $this->assertSame('Division1', $user->division);
        $this->assertSame('male', $user->gender);
        $this->assertSame('Etudiant', $user->contractType);
        $this->assertSame('2025-12-25', $user->getLastExitDate());
        $this->assertSame('Développeur', $user->position);
        $this->assertSame('72', $user->workingTime);
    }

    public function testDeserializationAcceptsNullGender(): void
    {
        $json = json_encode([
            'urn:ietf:params:scim:schemas:extension:javelo:2.0:User' => [
                'gender' => null,
            ],
        ]);

        /** @var User $user */
        $user = $this->serializer->deserialize($json, User::class, 'json');

        $this->assertNull($user->gender);
    }
}
