<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NonConformity;

use AppBundle\Controller\Quality\CrabController;
use AppBundle\Controller\Quality\FirstArticleQualification\FirstArticleQualificationController;
use AppBundle\Controller\Quality\NonConformityController;
use AppBundle\Form\Type\Quality\NonConformity\NonConformityAddType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'NonConformityDynamicForm', template: 'components/DynamicForm.html.twig')]
class NonConformityDynamicForm extends AbstractNonConformityDynamicForm
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?array $crab = null;

    #[LiveProp]
    public ?array $faq = null;

    #[LiveProp(writable: true, onUpdated: 'onEquipmentUpdated')]
    public array $selectedEquipment = [];

    #[LiveProp(writable: true, onUpdated: 'onProductsUpdated')]
    public array $selectedProducts = [];

    /** @var array<string, string> equipment record IRI => auto-added product IRI */
    #[LiveProp]
    public array $autoAddedProductByEquipment = [];

    public function mount(?string $crabId = null, ?string $faqId = null): void
    {
        if (null !== $crabId) {
            $this->crab = $this->client->find(CrabController::CRAB_URL, $crabId)->toArray();

            $crabProduct = $this->crab['equipmentRecord']['product']['@id'] ?? null;
            if (null !== $crabProduct) {
                $this->selectedProducts = [$crabProduct];
            }
        }

        if (null !== $faqId) {
            $this->faq = $this->client->find(FirstArticleQualificationController::RESOURCE_URL, $faqId)->toArray();

            $this->selectedEquipment = array_column($this->faq['equipmentRecords'] ?? [], '@id');
        }
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
    public function save(Request $request, #[LiveArg] string $clickedButton = 'no_parts_to_add'): RedirectResponse
    {
        $this->formValues['equipmentRecords'] = [];
        // products is bound to the selectedProducts LiveProp (data-model), so feed it to the form.
        $this->formValues['products'] = $this->selectedProducts;

        // Files are not part of the serialized data-model, so submitForm() never feeds them
        // to the form. Read the file before submitForm() so we can inject the error after
        // submit() runs (errors added before submit() are wiped by Form::submit()).
        $mainFile = $request->files->get($this->getForm()->getName())['mainFile'] ?? null;

        $importCrabPhoto = null !== $this->crab && filter_var($this->formValues['importCrabPhoto'] ?? false, \FILTER_VALIDATE_BOOLEAN);

        try {
            $this->submitForm();
        } catch (UnprocessableEntityHttpException $e) {
            $this->formValues['equipmentRecords'] = $this->selectedEquipment;
            if (!$mainFile instanceof UploadedFile && !$importCrabPhoto) {
                $this->handleFileError();
            }
            throw new $e();
        }

        if (!$mainFile instanceof UploadedFile && !$importCrabPhoto) {
            $this->handleFileError();
            throw new UnprocessableEntityHttpException();
        }

        try {
            $payload = [];
            if (null !== $this->crab) {
                $payload = ['crab' => $this->crab['@id']];
            } elseif (null !== $this->faq) {
                $payload = ['faq' => $this->faq['@id']];
            }
            $payload = array_merge($payload, $this->form->getData());
            $payload['equipmentRecords'] = $this->selectedEquipment;
            $nonConformity = $this->client->save(NonConformityController::NON_CONFORMITY_URL, $payload);

            if (!$importCrabPhoto) {
                $this->fileManager->uploadFile(
                    $nonConformity,
                    $mainFile,
                    NonConformityController::NON_CONFORMITY_URL,
                    null,
                    'main_file',
                    false,
                    true
                );
            }

            $this->addFlash('success', $this->translator->trans('non_conformity.success.add', [], 'non_conformity'));

            $redirectRoute = 'submit' === $clickedButton ? 'non_conformity_admin_parts' : 'non_conformity_show';

            return $this->redirectWithFlush($redirectRoute, ['id' => $nonConformity['id']]);
        } catch (ClientException $exception) {
            $this->formView = null;
            $this->formValues['equipmentRecords'] = $this->selectedEquipment;
            $this->violationMapper->mapToForm($exception, $this->form);

            throw new UnprocessableEntityHttpException();
        }
    }

    protected function instantiateForm(): FormInterface
    {
        if (null === $this->crab && null === $this->faq) {
            return $this->createForm(NonConformityAddType::class, [
                'reportedBy' => $this->tokenStorage->getToken()?->getUser()?->iriId ?? null,
                'iFactor' => 'IF1',
                'products' => [],
            ]);
        }

        if (null !== $this->crab) {
            $data = [
                'location' => $this->crab['equipmentRecord']['manufacturerLocation']['@id'] ?? null,
                'shortDescription' => $this->crab['description'],
                'products' => array_filter([$this->crab['equipmentRecord']['product']['@id'] ?? null]),
                'crabs' => [$this->crab['@id']],
                'reportedBy' => $this->crab['createdBy']['@id'] ?? $this->tokenStorage->getToken()?->getUser()?->iriId ?? null,
                'iFactor' => 'IF1',
            ];

            return $this->createForm(NonConformityAddType::class, $data, [
                'addFromCrab' => true,
            ]);
        }

        // FAQ branch
        $partNumbers = array_map(
            static fn (array $partNumber) => $partNumber['number'],
            $this->faq['partNumbers'] ?? []
        );

        $data = [
            'location' => $this->faq['location']['@id'] ?? null,
            'problem' => \sprintf(
                "Supplier: %s\n\nPart Numbers:\n%s",
                $this->faq['supplierName'] ?? '',
                implode("\n", $partNumbers)
            ),
            'equipmentRecords' => array_column($this->faq['equipmentRecords'] ?? [], '@id'),
            'faqs' => [$this->faq['@id']],
            'reportedBy' => $this->faq['poster']['@id'] ?? $this->tokenStorage->getToken()?->getUser()?->iriId ?? null,
            'iFactor' => 'IF1',
        ];

        return $this->createForm(NonConformityAddType::class, $data);
    }

    private function handleFileError(): void
    {
        $this->formView = null;
        $this->formValues['equipmentRecords'] = $this->selectedEquipment;
        $this->form->get('mainFile')->addError(
            new FormError($this->translator->trans('This value should not be blank.', [], 'validators'))
        );
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
