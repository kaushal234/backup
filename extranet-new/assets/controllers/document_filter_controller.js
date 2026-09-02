import { Controller } from '@hotwired/stimulus';

// Client-side filtering of the manual documents tables.
// Filters rows across every table by Factory Number and Description (AND logic),
// hides tables that have no matching row, and shows an "empty" message when nothing matches.
export default class extends Controller {
    static targets = ['factory', 'description', 'empty'];

    connect() {
        this.filter();
    }

    filter() {
        const factoryQuery = this.hasFactoryTarget ? this.factoryTarget.value.trim().toLowerCase() : '';
        const descriptionQuery = this.hasDescriptionTarget ? this.descriptionTarget.value.trim().toLowerCase() : '';

        let anyVisible = false;

        this.element.querySelectorAll('table').forEach((table) => {
            let tableHasVisibleRow = false;

            table.querySelectorAll('tbody tr').forEach((row) => {
                const factoryText = (row.querySelector('.js-doc-factory')?.textContent ?? '').trim().toLowerCase();
                const descriptionText = (row.querySelector('.js-doc-description')?.textContent ?? '').trim().toLowerCase();

                const matches = factoryText.includes(factoryQuery) && descriptionText.includes(descriptionQuery);
                row.classList.toggle('d-none', !matches);

                if (matches) {
                    tableHasVisibleRow = true;
                }
            });

            const container = table.closest('.et-tables');
            if (container) {
                container.classList.toggle('d-none', !tableHasVisibleRow);
            }

            if (tableHasVisibleRow) {
                anyVisible = true;
            }
        });

        if (this.hasEmptyTarget) {
            this.emptyTarget.classList.toggle('d-none', anyVisible);
        }
    }
}
