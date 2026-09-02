<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NonConformity;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Manager\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractNonConformityDynamicForm extends AbstractController
{
    public ?FormErrorIterator $errors = null;

    public function __construct(
        protected readonly Client $client,
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly TranslatorInterface $translator,
        protected readonly ViolationMapper $violationMapper,
        protected readonly FileManager $fileManager,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Force session write before redirecting from a LiveComponent action.
     * Without this, the session may not be committed to storage before the browser
     * navigates to the target page, causing flash messages to be missed.
     */
    protected function redirectWithFlush(string $route, array $parameters = []): RedirectResponse
    {
        $this->requestStack->getSession()->save();

        return $this->redirectToRoute($route, $parameters);
    }

    /**
     * @param string[] $previousValue
     * @param string[] $selectedEquipment
     *
     * @return string[] equipment record IRIs newly added since the previous selection
     */
    protected function getNewlySelectedEquipmentRecords(array $previousValue, array $selectedEquipment): array
    {
        return array_values(array_diff($selectedEquipment, $previousValue));
    }

    protected function fetchProductIriFromEquipment(string $id): ?string
    {
        $equipmentRecords = $this->client->find('equipment_records', $id);

        return $equipmentRecords?->product['@id'] ?? null;
    }

    /**
     * @param string[] $currentProducts
     *
     * @return string[]
     */
    protected function addProductToForm(array $currentProducts, string $productIri): array
    {
        if (!\in_array($productIri, $currentProducts, true)) {
            $currentProducts[] = $productIri;
        }

        return $currentProducts;
    }

    /**
     * Synchronise products with an equipment-record selection change.
     *
     * Added equipment records auto-add their product (recorded in $map so we know it was
     * auto-added). Removed equipment records drop their auto-added product, but only when no
     * other still-selected equipment record points to the same product. Products added manually
     * never appear in $map and are therefore left untouched.
     *
     * @param string[]              $previous equipment record IRIs before the change
     * @param string[]              $current  equipment record IRIs after the change
     * @param string[]              $products current product IRIs (modified in place)
     * @param array<string, string> $map      equipment record IRI => auto-added product IRI (modified in place)
     */
    protected function syncEquipmentChange(array $previous, array $current, array &$products, array &$map): void
    {
        foreach ($this->getNewlySelectedEquipmentRecords($previous, $current) as $equipmentRecord) {
            $productIri = $this->fetchProductIriFromEquipment(basename($equipmentRecord));
            if ($productIri) {
                $products = $this->addProductToForm($products, $productIri);
                $map[$equipmentRecord] = $productIri;
            }
        }

        foreach (array_diff($previous, $current) as $equipmentRecord) {
            $productIri = $map[$equipmentRecord] ?? null;
            unset($map[$equipmentRecord]);

            // Keep the product as long as another selected equipment record still references it.
            if (null !== $productIri && !\in_array($productIri, $map, true)) {
                $products = array_values(array_diff($products, [$productIri]));
            }
        }
    }

    /**
     * Synchronise equipment records with a product selection change.
     *
     * When a product that was auto-added by an equipment record is removed, the equipment records
     * pointing to it are removed too. Manually added products have no entry in $map, so removing
     * them never removes an equipment record.
     *
     * @param string[]              $previous  product IRIs before the change
     * @param string[]              $current   product IRIs after the change
     * @param string[]              $equipment current equipment record IRIs (modified in place)
     * @param array<string, string> $map       equipment record IRI => auto-added product IRI (modified in place)
     */
    protected function syncProductChange(array $previous, array $current, array &$equipment, array &$map): void
    {
        foreach (array_diff($previous, $current) as $productIri) {
            foreach (array_keys($map, $productIri, true) as $equipmentRecord) {
                $equipment = array_values(array_diff($equipment, [$equipmentRecord]));
                unset($map[$equipmentRecord]);
            }
        }
    }
}
