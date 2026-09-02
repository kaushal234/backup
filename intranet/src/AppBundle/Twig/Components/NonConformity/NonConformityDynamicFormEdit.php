<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NonConformity;

use AppBundle\Controller\Quality\NonConformityController;
use AppBundle\Form\Type\Quality\NonConformity\NonConformityEditType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'NonConformityDynamicFormEdit', template: 'components/DynamicForm.html.twig')]
class NonConformityDynamicFormEdit extends AbstractNonConformityDynamicForm
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public array $nonConformity;

    #[LiveProp]
    public array $authorizedFields = [];

    #[LiveProp(writable: true, onUpdated: 'onEquipmentUpdated')]
    public array $selectedEquipment = [];

    #[LiveProp(writable: true, onUpdated: 'onProductsUpdated')]
    public array $selectedProducts = [];

    /** @var array<string, string> equipment record IRI => auto-added product IRI */
    #[LiveProp]
    public array $autoAddedProductByEquipment = [];

    #[LiveProp(writable: true)]
    public ?string $selectedSupplierNumber = null;

    public function mount(int $nonConformityId): void
    {
        $this->nonConformity = $this->client->find(NonConformityController::NON_CONFORMITY_URL, $nonConformityId)->toArray();

        $this->authorizedFields = $this->client->get('/fields', ['query' => ['iri' => $this->nonConformity['@id'], 'method' => 'PUT']]);

        foreach ($this->nonConformity['equipmentRecords'] ?? [] as $equipmentRecord) {
            $this->selectedEquipment[] = $equipmentRecord['@id'];
        }
        $this->selectedEquipment = array_values(array_unique($this->selectedEquipment));

        $this->nonConformity['equipmentRecords'] = $this->selectedEquipment;

        $previousProducts = [];
        foreach ($this->nonConformity['products'] as $product) {
            $previousProducts[] = $product['@id'];
        }
        $this->nonConformity['products'] = $previousProducts;

        $this->selectedProducts = $previousProducts;

        foreach ($this->selectedEquipment as $equipmentIri) {
            $productIri = $this->fetchProductIriFromEquipment(basename($equipmentIri));
            if ($productIri && \in_array($productIri, $previousProducts, true)) {
                $this->autoAddedProductByEquipment[$equipmentIri] = $productIri;
            }
        }

        $this->nonConformity['processes'] = array_map(
            static fn (array $process) => $process['@id'],
            $this->nonConformity['processes'] ?? []
        );

        $this->nonConformity['responsibles'] = array_map(
            static fn (array $responsible) => $responsible['@id'],
            $this->nonConformity['responsibles'] ?? []
        );

        foreach (['reportedBy', 'repairApprover'] as $field) {
            if (\is_array($this->nonConformity[$field] ?? null)) {
                $this->nonConformity[$field] = $this->nonConformity[$field]['@id'] ?? null;
            }
        }

        $this->selectedSupplierNumber = $this->nonConformity['supplierNumber'] ?? null;
    }

    public function onEquipmentUpdated(array $previousValue): void
    {
        $this->syncEquipmentChange(
            $previousValue,
            $this->selectedEquipment,
            $this->selectedProducts,
            $this->autoAddedProductByEquipment,
        );

        $this->syncFormValues();
    }

    public function onProductsUpdated(array $previousValue): void
    {
        $this->syncProductChange(
            $previousValue,
            $this->selectedProducts,
            $this->selectedEquipment,
            $this->autoAddedProductByEquipment,
        );

        $this->syncFormValues();
    }

    #[LiveAction]
    public function save(Request $request): RedirectResponse
    {
        $this->formValues['equipmentRecords'] = $this->selectedEquipment;
        // products is bound to the selectedProducts LiveProp (data-model), so feed it to the form.
        $this->formValues['products'] = $this->selectedProducts;

        $this->submitForm();

        try {
            $ncrSaved = $this->client->save(NonConformityController::NON_CONFORMITY_URL, $this->form->getData());
            // Files are not part of the serialized data-model, so submitForm() never feeds them
            // to the form: read the uploaded file straight from the request instead.
            $mainFile = $request->files->get($this->form->getName())['mainFile'] ?? null;
            if ($mainFile instanceof UploadedFile) {
                $this->fileManager->uploadFile($ncrSaved, $mainFile, NonConformityController::NON_CONFORMITY_URL, null, 'main_file', false, true);
            }
            $this->addFlash('success', $this->translator->trans('non_conformity.success.edition', [], 'non_conformity'));

            return $this->redirectWithFlush('non_conformity_show', ['id' => $this->nonConformity['id']]);
        } catch (ClientException $exception) {
            $this->formView = null;
            $this->formValues['equipmentRecords'] = $this->selectedEquipment ?? [];
            $this->violationMapper->mapToForm($exception, $this->form);

            throw new UnprocessableEntityHttpException();
        }
    }

    protected function instantiateForm(): FormInterface
    {
        $this->nonConformity['equipmentRecords'] = $this->selectedEquipment;

        return $this->createForm(NonConformityEditType::class, $this->nonConformity, [
            'authorized_fields' => $this->authorizedFields,
        ]);
    }

    /**
     * Mirror both selections onto the form values so each <select> re-renders and its TomSelect
     * widget reflects server-side changes (e.g. removing a model removes its equipment record).
     */
    private function syncFormValues(): void
    {
        $this->formValues['products'] = $this->selectedProducts;
        $this->formValues['equipmentRecords'] = $this->selectedEquipment;
    }

    /** @used-by \Symfony\UX\LiveComponent\ComponentWithFormTrait @phpstan-ignore-next-line */
    private function getDataModelValue(): ?string
    {
        return 'norender|*';
    }
}
