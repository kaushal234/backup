<?php

declare(strict_types=1);

namespace App\Tests\Form\Type\Directory\People;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\People\PeopleChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleChoiceTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testConfigureOptions(): void
    {
        $dataProvider = $this->createMock(DataProvider::class);

        $dataProvider
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['@id' => '/people/1', 'lastname' => 'Doe', 'firstname' => 'John', 'email' => 'john@example.com'],
                ['@id' => '/people/2', 'lastname' => 'Smith', 'firstname' => 'Jane', 'email' => 'jane@example.com'],
            ]);

        $formType = new PeopleChoiceType($dataProvider);

        $resolver = new OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $this->assertSame([], $options['exclude']);
        $this->assertSame('@id', $options['key']);

        $this->assertSame(
            [
                'hidden' => 0,
                'disabled' => 0,
                'pagination' => 0,
                'normalization_groups_override' => ['people_list'],
            ],
            $options['filters']);

        $this->assertFalse($options['choice_translation_domain']);
        $this->assertSame([], $options['extra_choices']);
    }

    public function testChoicesAreTransformedCorrectly(): void
    {
        $dataProvider = $this->createMock(DataProvider::class);

        $dataProvider
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                [
                    '@id' => '/people/1',
                    'lastname' => 'Doe',
                    'firstname' => 'John',
                    'email' => 'john@example.com',
                ],
                [
                    '@id' => '/people/2',
                    'lastname' => 'Smith',
                    'firstname' => 'Jane',
                    'email' => 'jane@example.com',
                ],
            ]);

        $formType = new PeopleChoiceType($dataProvider);

        $resolver = new OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve();

        $choices = $options['choices'];

        $this->assertArrayHasKey('Doe, John - john@example.com', $choices);
        $this->assertArrayHasKey('Smith, Jane - jane@example.com', $choices);
        $this->assertSame('/people/1', $choices['Doe, John - john@example.com']);
        $this->assertSame('/people/2', $choices['Smith, Jane - jane@example.com']);
    }

    public function testExclusionWorks(): void
    {
        $dataProvider = $this->createMock(DataProvider::class);

        $dataProvider
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                [
                    '@id' => '/people/1',
                    'lastname' => 'Doe',
                    'firstname' => 'John',
                    'email' => 'john@example.com',
                ],
                [
                    '@id' => '/people/2',
                    'lastname' => 'Smith',
                    'firstname' => 'Jane',
                    'email' => 'jane@example.com',
                ],
            ]);
        $formType = new PeopleChoiceType($dataProvider);

        $resolver = new OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve([
            'exclude' => [['@id' => '/people/1']],
        ]);

        $choices = $options['choices'];

        $this->assertArrayNotHasKey('Doe, John - john@example.com', $choices);
        $this->assertArrayHasKey('Smith, Jane - jane@example.com', $choices);
    }

    public function testParentType(): void
    {
        $dataProvider = $this->createMock(DataProvider::class);

        $dataProvider
            ->expects($this->never())
            ->method('findAll');
        $formType = new PeopleChoiceType($dataProvider);
        $this->assertSame(SelectFormType::class, $formType->getParent());
    }

    public function testBlockPrefix(): void
    {
        $dataProvider = $this->createMock(DataProvider::class);

        $dataProvider
            ->expects($this->never())
            ->method('findAll');
        $formType = new PeopleChoiceType($dataProvider);
        $this->assertSame('app_people_choice', $formType->getBlockPrefix());
    }
}
