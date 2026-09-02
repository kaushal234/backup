<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\NonConformity;

use AppBundle\Controller\Quality\NonConformityController;
use AppBundle\Twig\Components\NonConformity\NonConformityDynamicFormEdit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class NonConformityDynamicFormEditTest extends LiveComponentTestCase
{
    public function testMountExtractsEquipmentRecordIrisIntoSelectedEquipment(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'equipmentRecords' => [
                ['@id' => '/equipment_records/10'],
                ['@id' => '/equipment_records/20'],
            ],
        ]));

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        self::assertSame(['/equipment_records/10', '/equipment_records/20'], $component->selectedEquipment);
    }

    public function testMountDeduplicatesEquipmentRecords(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'equipmentRecords' => [
                ['@id' => '/equipment_records/10'],
                ['@id' => '/equipment_records/10'],
            ],
        ]));

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        self::assertSame(['/equipment_records/10'], $component->selectedEquipment);
    }

    public function testMountPrePopulatesProductsInFormValues(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'products' => [['@id' => '/sale/products/100']],
        ]));

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        self::assertContains('/sale/products/100', $component->formValues['products']);
        self::assertContains('/sale/products/100', $component->selectedProducts);
    }

    public function testSelectingEquipmentRecordAddsProductToForm(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        self::assertContains('/sale/products/456', $component->formValues['products']);
    }

    public function testDeselectingEquipmentRecordRemovesAutoAddedProduct(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

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

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        $component->selectedEquipment = ['/equipment_records/123'];
        $component->onEquipmentUpdated([]);

        // Removing that product removes the equipment record that added it.
        $component->selectedProducts = [];
        $component->onProductsUpdated(['/sale/products/456']);

        self::assertNotContains('/equipment_records/123', $component->selectedEquipment);
    }

    public function testRemovingExistingManualProductDoesNotRemoveEquipmentRecord(): void
    {
        $this->login('superuser');

        // The NCR has an ER whose product is DIFFERENT from the manually-added product.
        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'equipmentRecords' => [['@id' => '/equipment_records/10']],
            'products' => [['@id' => '/sale/products/100']],
        ]));
        // mount() fetches each ER's product; ER 10 points to a different product.
        $this->mockApi('equipment_records/10', [
            '@id' => '/equipment_records/10',
            'product' => ['@id' => '/sale/products/999'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        // Removing the pre-existing manual product must not remove the unrelated equipment record.
        $component->selectedProducts = [];
        $component->onProductsUpdated(['/sale/products/100']);

        self::assertContains('/equipment_records/10', $component->selectedEquipment);
    }

    public function testRemovingExistingLinkedEquipmentRecordRemovesProduct(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'equipmentRecords' => [['@id' => '/equipment_records/10']],
            'products' => [['@id' => '/sale/products/100']],
        ]));
        $this->mockApi('equipment_records/10', [
            '@id' => '/equipment_records/10',
            'product' => ['@id' => '/sale/products/100'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        $component->selectedEquipment = [];
        $component->onEquipmentUpdated(['/equipment_records/10']);

        self::assertNotContains('/sale/products/100', $component->selectedProducts);
    }

    public function testRemovingExistingLinkedProductRemovesEquipmentRecord(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(1, [
            'equipmentRecords' => [['@id' => '/equipment_records/10']],
            'products' => [['@id' => '/sale/products/100']],
        ]));
        $this->mockApi('equipment_records/10', [
            '@id' => '/equipment_records/10',
            'product' => ['@id' => '/sale/products/100'],
        ]);

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        $component->selectedProducts = [];
        $component->onProductsUpdated(['/sale/products/100']);

        self::assertNotContains('/equipment_records/10', $component->selectedEquipment);
    }

    public function testRemovingProductRemovesEquipmentRecordAcrossRequests(): void
    {
        // Full client round-trip: set() dehydrates/rehydrates the component like the browser,
        // verifying the ER->product map survives between requests.
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        $this->mockApi('equipment_records/123', [
            '@id' => '/equipment_records/123',
            'product' => ['@id' => '/sale/products/456'],
        ]);

        $test = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1]);

        $test->set('selectedEquipment', ['/equipment_records/123']);
        self::assertContains('/sale/products/456', $test->component()->selectedProducts);

        $test->set('selectedProducts', []);

        self::assertNotContains('/equipment_records/123', $test->component()->selectedEquipment);
    }

    public function testOnEquipmentUpdatedSyncsEquipmentRecordsToFormValues(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        $component->selectedEquipment = ['/equipment_records/10', '/equipment_records/20'];
        $component->onEquipmentUpdated([]);

        self::assertSame(['/equipment_records/10', '/equipment_records/20'], $component->formValues['equipmentRecords']);
    }

    public function testSaveRedirectsToShowPage(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());

        $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->call('save');

        self::assertTrue($this->browser->getResponse()->isRedirect());
        self::assertStringContainsString(
            '/quality/non-conformities/1/show',
            $this->browser->getResponse()->headers->get('Location')
        );
    }

    public function testSaveUploadsMainFileWhenProvided(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        // NonConformityEditType disables mainFile unless FEATURE_NON_CONFORMITY_EDIT is granted.
        // The FeatureProvider resolves features via the API, so we mock the endpoint here.
        $this->mockApi('features', [
            'hydra:member' => [['name' => 'FEATURE_NON_CONFORMITY_EDIT']],
            'hydra:totalItems' => 1,
        ]);

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

        $component = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->component();

        // Files are not part of the serialized data-model: save() reads the upload from the
        // request, keyed by the form name, so build a Request carrying it.
        $tmpPath = tempnam(sys_get_temp_dir(), 'ncr_edit_test_');
        file_put_contents($tmpPath, '%PDF-fake');
        $mainFile = new UploadedFile($tmpPath, 'picture.pdf', 'application/pdf', null, true);

        $request = new Request();
        $request->files->set('non_conformity_edit', ['mainFile' => $mainFile]);

        $component->save($request);
    }

    public function testSaveMapsApiViolationsWhenClientExceptionThrown(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture(), 200, 'GET');
        $this->mockApi('quality/non_conformities/1', [
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'violations' => [['propertyPath' => 'shortDescription', 'message' => 'Too short.']],
        ], 422, 'PUT');

        $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1])->call('save');

        self::assertSame(422, $this->browser->getResponse()->getStatusCode());
    }

    public function testSaveSendsSelectedProductsToApi(): void
    {
        $this->login('superuser');

        $this->mockApi('quality/non_conformities/1', $this->ncrFixture());
        // Fields absent from /fields are disabled by NonConformityEditType, so authorize products.
        // Declared as GET so it is matched before the method-less NCR mock (the /fields URL carries
        // the NCR iri in its query string and would otherwise match the NCR mock first).
        $this->mockApi('fields', ['products', 'equipmentRecords'], 200, 'GET');

        // products is driven by the selectedProducts LiveProp; save() feeds it to the form.
        $testComponent = $this->createLiveComponent(NonConformityDynamicFormEdit::class, ['nonConformityId' => 1]);
        $testComponent->set('selectedProducts', ['/sale/products/456']);
        $testComponent->call('save');

        $body = $this->getLastCapturedApiRequestBody();
        self::assertNotNull($body);
        self::assertSame(['/sale/products/456'], $body['products']);
    }

    private function ncrFixture(int $id = 1, array $overrides = []): array
    {
        return array_merge([
            '@id' => \sprintf('/quality/non_conformities/%d', $id),
            'id' => $id,
            'equipmentRecords' => [],
            'products' => [],
            'mainFile' => null,
        ], $overrides);
    }
}
