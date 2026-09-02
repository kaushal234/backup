<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\NonConformity;

use AppBundle\Controller\Quality\NonConformityController;
use AppBundle\Twig\Components\NonConformity\NonConformityDynamicForm;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class NonConformityDynamicFormTest extends LiveComponentTestCase
{
    public function testSelectingEquipmentRecordAddsProductToForm(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        self::assertContains('/sale/products/456', $component->formValues['products']);
    }

    public function testSelectingEquipmentRecordWithoutProductDoesNotAddProduct(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/456', [
            '@id' => '/equipment_records/456',
            'product' => null,
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/456'];
        $component->onEquipmentUpdated([]);

        self::assertEmpty($component->formValues['products']);
    }

    public function testSelectingMultipleEquipmentRecordsAccumulatesProducts(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/111'],
        ]);
        $this->mockApi('equipment_records/456', [
            '@id' => '/equipment_records/456',
            'product' => ['@id' => '/sale/products/222'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        $component->selectedEquipment = ['/equipment_records/123', '/equipment_records/456'];
        $component->onEquipmentUpdated(['/equipment_records/123']);

        self::assertContains('/sale/products/111', $component->formValues['products']);
        self::assertContains('/sale/products/222', $component->formValues['products']);
    }

    public function testSelectingMultipleEquipmentRecordsAtOnceAddsAllProducts(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/111'],
        ]);
        $this->mockApi('equipment_records/456', [
            '@id' => '/equipment_records/456',
            'product' => ['@id' => '/sale/products/222'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        // Both equipment records appear in a single update (the diff holds two new IRIs).
        // The loop must add every product, not only the first one.
        $component->selectedEquipment = ['/equipment_records/123', '/equipment_records/456'];
        $component->onEquipmentUpdated([]);

        self::assertContains('/sale/products/111', $component->formValues['products']);
        self::assertContains('/sale/products/222', $component->formValues['products']);
    }

    public function testDeselectingEquipmentRecordRemovesAutoAddedProduct(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        // Removing the equipment record removes the product it auto-added.
        $component->selectedEquipment = [];
        $component->onEquipmentUpdated(['/equipment_records/123']);

        self::assertNotContains('/sale/products/456', $component->selectedProducts);
    }

    public function testRemovingAutoAddedProductRemovesAssociatedEquipmentRecord(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        // Select the equipment record → its product is auto-added and linked.
        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        // Removing that product removes the equipment record that added it.
        $component->selectedProducts = [];
        $component->onProductsUpdated(['/sale/products/456']);

        self::assertNotContains('/equipment_records/123', $component->selectedEquipment);
    }

    public function testRemovingOneOfTwoEquipmentRecordsSharingProductKeepsProductUntilLast(): void
    {
        $this->login('superuser');

        // Two distinct equipment records pointing to the same product.
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);
        $this->mockApi('equipment_records/789', [
            '@id' => '/equipment_records/789',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123', '/equipment_records/789'];
        $component->onEquipmentUpdated([]);

        // Removing one equipment record keeps the shared product (the other still references it).
        $component->selectedEquipment = ['/equipment_records/789'];
        $component->onEquipmentUpdated(['/equipment_records/123', '/equipment_records/789']);
        self::assertContains('/sale/products/456', $component->selectedProducts);

        // Removing the last referencing equipment record finally removes the product.
        $component->selectedEquipment = [];
        $component->onEquipmentUpdated(['/equipment_records/789']);
        self::assertNotContains('/sale/products/456', $component->selectedProducts);
    }

    public function testManuallyAddedProductSurvivesEquipmentRemovalAndKeepsEquipment(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        // Equipment record auto-adds its product.
        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        // A product added manually (no equipment record points to it).
        $component->selectedProducts = ['/sale/products/456', '/sale/products/999'];
        $component->onProductsUpdated(['/sale/products/456']);

        // Removing an unrelated equipment record must not remove the manually added product.
        $component->selectedEquipment = [];
        $component->onEquipmentUpdated(['/equipment_records/123']);
        self::assertContains('/sale/products/999', $component->selectedProducts);

        // Removing the manually added product must not remove any equipment record.
        $component->selectedProducts = [];
        $component->onProductsUpdated(['/sale/products/999']);
        self::assertEmpty($component->selectedEquipment);
    }

    public function testRemovingProductRemovesEquipmentRecordAcrossRequests(): void
    {
        // Full client round-trip: set() dehydrates/rehydrates the component like the browser.
        // This verifies the ER->product map survives between the equipment-selection request
        // and the later product-removal request.
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $test = $this->createLiveComponent(NonConformityDynamicForm::class);

        // Request 1: select the equipment record (auto-adds the product + records the mapping).
        $test->set('selectedEquipment', ['/equipment_records/123']);
        self::assertContains('/sale/products/456', $test->component()->selectedProducts);

        // Request 2: remove the product → its equipment record must be removed too.
        $test->set('selectedProducts', []);

        self::assertNotContains('/equipment_records/123', $test->component()->selectedEquipment);
    }

    public function testReselectingEquipmentRecordDoesNotDuplicateProduct(): void
    {
        $this->login('superuser');

        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        // Select → product added.
        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        // Deselect → product removed along with the equipment record.
        $component->selectedEquipment = [];
        $component->onEquipmentUpdated(['/equipment_records/123']);

        // Re-select → the product is added again, exactly once (no duplicate).
        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        self::assertCount(1, $component->selectedProducts);
    }

    public function testTwoEquipmentRecordsSharingProductAddItOnce(): void
    {
        $this->login('superuser');

        // Two distinct equipment records pointing to the same product.
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);
        $this->mockApi('equipment_records/789', [
            '@id' => '/equipment_records/789',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123', '/equipment_records/789'];
        $component->onEquipmentUpdated([]);

        self::assertSame(['/sale/products/456'], array_values($component->formValues['products']));
    }

    public function testSelectingSameEquipmentTwiceDoesNotDuplicateProduct(): void
    {
        $this->login('superuser');
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);
        $component->onEquipmentUpdated(['/equipment_records/123']);

        self::assertCount(1, $component->formValues['products']);
    }

    public function testMountingWithCrabIdPrefillsForm(): void
    {
        $this->login('superuser');

        $this->mockApi('crabs/789', [
            '@id' => '/crabs/789',
            'description' => 'Crack in fuselage',
            'createdBy' => ['@id' => '/people/12'],
            'equipmentRecord' => [
                'product' => ['@id' => '/sale/products/999'],
                'manufacturerLocation' => ['@id' => '/locations/42'],
            ],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class, ['crabId' => '789'])->component();

        self::assertSame('Crack in fuselage', $component->formValues['shortDescription']);
        self::assertContains('/sale/products/999', $component->formValues['products']);
    }

    public function testMountingWithCrabWithoutProductLeavesProductsEmpty(): void
    {
        $this->login('superuser');

        $this->mockApi('crabs/789', [
            '@id' => '/crabs/789',
            'description' => 'Crack in fuselage',
            'createdBy' => ['@id' => '/people/12'],
            'equipmentRecord' => [
                'product' => null,
                'manufacturerLocation' => ['@id' => '/locations/42'],
            ],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class, ['crabId' => '789'])->component();

        // A CRAB with no product must yield a flat empty array, never [[]] (an array
        // wrapping an empty array), which would be an invalid value for the product field.
        self::assertEmpty($component->formValues['products']);
    }

    public function testMountingWithCrabWithoutCreatedByFallsBackToCurrentUser(): void
    {
        $this->login('superuser');

        $this->mockApi('crabs/789', [
            '@id' => '/crabs/789',
            'description' => 'Crack in fuselage',
            // No createdBy and no manufacturerLocation: mounting must not raise a 500.
            'equipmentRecord' => [
                'product' => ['@id' => '/sale/products/999'],
            ],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class, ['crabId' => '789'])->component();

        // reportedBy falls back to the logged-in user when the CRAB has no createdBy.
        self::assertSame('/people/12', $component->formValues['reportedBy']);
    }

    public function testFormInitializesReportedByWithLoggedInUser(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(NonConformityDynamicForm::class)->component();

        self::assertSame('/people/12', $component->formValues['reportedBy']);
    }

    public function testMountWithCrabIdEnablesImportCrabPhotoField(): void
    {
        $this->login('superuser');

        $this->mockApi('crabs/789', [
            '@id' => '/crabs/789',
            'description' => 'Crack in fuselage',
            'createdBy' => ['@id' => '/people/12'],
            'equipmentRecord' => [
                'product' => ['@id' => '/sale/products/999'],
                'manufacturerLocation' => ['@id' => '/locations/42'],
            ],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicForm::class, ['crabId' => '789'])->component();

        // importCrabPhoto is only added to the form when the addFromCrab option is true.
        // FormView normalizes checkbox values to string ('1' when checked).
        self::assertArrayHasKey('importCrabPhoto', $component->formValues);
        self::assertSame('1', $component->formValues['importCrabPhoto']);
    }

    public function testSaveRedirectsToShowWhenNoPartsToAddClicked(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        self::assertTrue($this->browser->getResponse()->isRedirect());
        self::assertStringContainsString(
            '/quality/non-conformities/4/show',
            $this->browser->getResponse()->headers->get('Location')
        );
    }

    public function testSaveWithSubmitButtonRedirectsToAdminPart(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', ['clickedButton' => 'submit'], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        self::assertTrue($this->browser->getResponse()->isRedirect());
        self::assertStringContainsString(
            '/quality/non-conformities/4/admin-parts',
            $this->browser->getResponse()->headers->get('Location')
        );
    }

    public function testSaveUploadsMainFileWhenProvided(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        // FileManager must be mocked before creating the component so the mock
        // is injected via the constructor (readonly property).
        $fileManager = $this->mockFileManager();
        $fileManager->expects(self::once())
            ->method('uploadFile')
            ->with(
                self::anything(),
                self::isInstanceOf(UploadedFile::class),
                NonConformityController::NON_CONFORMITY_URL,
                null,
                'main_file',
                false,
                true
            );

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSaveSendsSelectedEquipmentAsEquipmentRecordsToApi(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->set('selectedEquipment', ['/equipment_records/123', '/equipment_records/456']);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        $body = $this->getApiRequestBodyFor();
        self::assertNotNull($body);
        self::assertSame(['/equipment_records/123', '/equipment_records/456'], $body['equipmentRecords']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSaveSendsSelectedProductsToApi(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->set('selectedProducts', ['/sale/products/456']);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        $body = $this->getApiRequestBodyFor();
        self::assertNotNull($body);
        self::assertSame(['/sale/products/456'], $body['products']);
    }

    public function testSaveWithNoSelectedProductsRedirects(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', ['@id' => '/quality/non_conformities/4', 'id' => 4]);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        self::assertTrue($this->browser->getResponse()->isRedirect());
    }

    public function testSaveMapsApiViolationsWhenClientExceptionThrown(): void
    {
        $this->login('superuser');
        $this->mockApi('quality/non_conformities', [
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'violations' => [['propertyPath' => 'shortDescription', 'message' => 'Too short.']],
        ], 422);
        $this->mockLocations();

        $wrapper = $this->createLiveComponent(NonConformityDynamicForm::class);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', [], ['non_conformity_add' => ['mainFile' => $this->makePdfUpload()]]);

        self::assertSame(422, $this->browser->getResponse()->getStatusCode());
    }

    private function mockLocations(): void
    {
        $this->mockApi('locations', [
            'hydra:member' => [[
                '@id' => '/locations/20',
                'id' => 20,
                'name' => 'Test Location',
                'erp' => 'ERP1',
                'iri' => '/locations/20',
            ]],
            'hydra:totalItems' => 1,
        ]);
    }

    private function makePdfUpload(): UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'ncr_test_');
        file_put_contents($tmpPath, '%PDF-fake');

        return new UploadedFile($tmpPath, 'picture.pdf', 'application/pdf', null, true);
    }

    private function setValidFormValues(object $wrapper): void
    {
        $wrapper->set('non_conformity_add.location', '/locations/20');
        $wrapper->set('non_conformity_add.reportedBy', '/people/12');
        $wrapper->set('non_conformity_add.shortDescription', 'test');
        $wrapper->set('non_conformity_add.problem', 'test');
        $wrapper->set('non_conformity_add.iFactor', 'IF1');
    }

    /**
     * @throws \ReflectionException
     */
    private function getApiRequestBodyFor(): ?array
    {
        $prop = (new \ReflectionClass(parent::class))->getProperty('capturedApiRequests');

        foreach ($prop->getValue($this) as $request) {
            if (str_contains($request['url'], 'quality/non_conformities') && null !== $request['body']) {
                return $request['body'];
            }
        }

        return null;
    }
}
