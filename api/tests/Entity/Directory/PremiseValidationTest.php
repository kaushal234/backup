<?php

declare(strict_types=1);

namespace App\Tests\Entity\Directory;

use App\Entity\AddressWithCountry;
use App\Entity\Directory\Premise;
use App\Entity\Directory\PremiseTag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PremiseValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider premiseDataProvider
     */
    public function testPremiseValidation(
        ?string $name,
        ?string $description,
        ?float $latitude,
        ?float $longitude,
        bool $archived,
        array $expectedMessages
    ): void {
        $tag = new PremiseTag();
        $tag->setName('Some Tag');

        $premise = new Premise();
        $premise->addTag($tag);

        $premise->name = $name;
        $premise->description = $description;
        $premise->archived = $archived;
        $premise->latitude = $latitude;
        $premise->longitude = $longitude;

        $address = new AddressWithCountry();
        $address->setStreet1('Some street');
        $address->setPostalCode('12345');
        $address->setCity('City');
        $address->setCountry('FR');
        $premise->address = $address;

        $errors = $this->validator->validate($premise);

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        foreach ($expectedMessages as $expectedMessage) {
            $this->assertContains($expectedMessage, $errorMessages);
        }

        $this->assertCount(\count($expectedMessages), $errors);
    }

    public function premiseDataProvider(): array
    {
        return [
            'all valid' => [
                'name' => 'Prem1',
                'description' => 'Desc valid',
                'latitude' => 45.75,
                'longitude' => 4.85,
                'archived' => false,
                'expectedMessages' => [],
            ],
            'latitude out of range' => [
                'name' => 'Prem1',
                'description' => 'Desc valid',
                'latitude' => 95.0,
                'longitude' => 4.85,
                'archived' => false,
                'expectedMessages' => ['Latitude must be between -90 and 90.'],
            ],
            'longitude out of range' => [
                'name' => 'Prem1',
                'description' => 'Desc valid',
                'latitude' => 45.75,
                'longitude' => 185.0,
                'archived' => false,
                'expectedMessages' => ['Longitude must be between -180 and 180.'],
            ],
            'latitude not null' => [
                'name' => 'Prem1',
                'description' => 'Desc valid',
                'latitude' => null,
                'longitude' => 160.0,
                'archived' => false,
                'expectedMessages' => ['This value should not be null.'],
            ],
            'longitude not null' => [
                'name' => 'Prem1',
                'description' => 'Desc valid',
                'latitude' => 45.75,
                'longitude' => null,
                'archived' => false,
                'expectedMessages' => ['This value should not be null.'],
            ],
        ];
    }
}
