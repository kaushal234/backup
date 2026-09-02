<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Form;

use ApiBundle\Form\ViolationMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class ViolationMapperTest extends TestCase
{
    private FormFactoryInterface $formFactory;

    private ViolationMapper $mapper;

    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->mapper = new ViolationMapper(PropertyAccess::createPropertyAccessor());
    }

    public function testFlatViolationIsMappedOntoTheTargetedItemField(): void
    {
        $form = $this->buildCollectionForm(2);
        $body = $this->violationBody('This value should be greater than 100.');

        $this->mapper->contentMapToFormCollection($body, $form->get('forecastClosures'), 1);

        self::assertSame(
            ['This value should be greater than 100.'],
            $this->itemFieldErrors($form, 1)
        );
        self::assertSame([], $this->itemFieldErrors($form, 0));
        self::assertSame([], $this->rootErrors($form));
    }

    public function testTwoItemsEachGetTheirOwnError(): void
    {
        $form = $this->buildCollectionForm(2);
        $collection = $form->get('forecastClosures');

        $this->mapper->contentMapToFormCollection(
            $this->violationBody('Error on item 0.'),
            $collection,
            0
        );
        $this->mapper->contentMapToFormCollection(
            $this->violationBody('Error on item 1.'),
            $collection,
            1
        );

        self::assertSame(['Error on item 0.'], $this->itemFieldErrors($form, 0));
        self::assertSame(['Error on item 1.'], $this->itemFieldErrors($form, 1));
        self::assertSame([], $this->rootErrors($form));
    }

    public function testUnknownIndexFallsBackToGlobalError(): void
    {
        $form = $this->buildCollectionForm(1);
        $body = $this->violationBody('This value should be greater than 100.');

        $this->mapper->contentMapToFormCollection($body, $form->get('forecastClosures'), 1);

        self::assertSame([], $this->itemFieldErrors($form, 0));
        $rootErrors = $this->rootErrors($form);
        self::assertCount(1, $rootErrors);
        self::assertStringContainsString('This value should be greater than 100.', $rootErrors[0]);
    }

    private function buildCollectionForm(int $itemCount): FormInterface
    {
        $data = array_fill(0, $itemCount, ['status' => '', 'orderedQuantity' => 0]);

        return $this->formFactory
            ->createNamedBuilder('')
            ->add('forecastClosures', CollectionType::class, [
                'entry_type' => TestItemType::class,
                'allow_add' => true,
                'data' => $data,
            ])
            ->getForm();
    }

    /**
     * Helper: an API-shaped 422 body with a single flat-path violation.
     */
    private function violationBody(string $message): array
    {
        return [
            '@context' => '/contexts/ConstraintViolation',
            '@type' => 'ConstraintViolation',
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'orderedQuantity',
                    'message' => $message,
                    'code' => 'some-code',
                ],
            ],
            'detail' => 'orderedQuantity: '.$message,
            'hydra:description' => 'orderedQuantity: '.$message,
            'hydra:title' => 'An error occurred',
        ];
    }

    /**
     * Returns the error messages attached to a given field of a given item.
     *
     * @return string[]
     */
    private function itemFieldErrors(FormInterface $form, int $index): array
    {
        $fieldForm = $form->get('forecastClosures')->get((string) $index)->get('orderedQuantity');

        $messages = [];
        foreach ($fieldForm->getErrors() as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }

    /**
     * Returns the error messages attached to the root form (global errors).
     *
     * @return string[]
     */
    private function rootErrors(FormInterface $form): array
    {
        $messages = [];
        foreach ($form->getErrors() as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }
}

class TestItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', TextType::class)
            ->add('orderedQuantity', IntegerType::class);
    }
}
