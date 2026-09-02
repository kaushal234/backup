import { Controller } from '@hotwired/stimulus';

/*
 * equipment-search — redirects the homepage finder forms to the equipment list
 * with the correct Kreyu DataTable filter pre-filled.
 *
 * Usage:
 *   <form data-controller="equipment-search"
 *         data-equipment-search-filter-value="__search"
 *         data-equipment-search-url-value="{{ path('equipment:index') }}">
 *     <input data-equipment-search-target="input" type="text">
 *     <button type="submit">Find</button>
 *   </form>
 */
export default class extends Controller {
    static targets = ['input'];
    static values  = { filter: String, url: String };

    connect() {
        this.element.addEventListener('submit', (e) => {
            e.preventDefault();
            this.search();
        });
    }

    search() {
        const q = this.hasInputTarget ? this.inputTarget.value.trim() : '';
        if (!q) return;

        const params = new URLSearchParams({
            page_equipment_record: 1,
            limit_equipment_record: 25,
        });
        params.set(`filter_equipment_record[${this.filterValue}][value]`, q);

        const base = this.hasUrlValue ? this.urlValue : '/equipments';
        window.location.href = `${base}?${params.toString()}`;
    }
}
