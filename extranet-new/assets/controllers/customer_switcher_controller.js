import { Controller } from '@hotwired/stimulus';

/*
 * customer-switcher — client-side search/filter for the "Switch customer" panel.
 *
 * Filters the company list as the user types, highlights matching characters,
 * hides alphabetical group headers that have no visible entry left, and shows a
 * "no company found" message when nothing matches. Switching itself stays a plain
 * link to the switch_customer route.
 *
 * Usage:
 *   <li data-controller="customer-switcher">
 *     <input data-customer-switcher-target="search"
 *            data-action="input->customer-switcher#filter click->customer-switcher#stop">
 *     <ul data-customer-switcher-target="list">
 *       <li data-customer-switcher-target="group">A</li>
 *       <li data-customer-switcher-target="item" data-cs-name="acme" data-cs-group="A">
 *         <a><span class="cs-label">Acme</span></a>
 *       </li>
 *       <li data-customer-switcher-target="empty" class="d-none">No company found</li>
 *     </ul>
 *   </li>
 */
export default class extends Controller {
    static targets = ['search', 'item', 'group', 'empty'];

    connect() {
        // Focus the search field and reset the filter whenever the dropdown opens.
        this.dropdown = this.element.closest('.dropdown');
        if (this.dropdown) {
            this.onShown = () => {
                this.reset();
                this.searchTarget.focus();
            };
            this.dropdown.addEventListener('shown.bs.dropdown', this.onShown);
        }
    }

    disconnect() {
        if (this.dropdown && this.onShown) {
            this.dropdown.removeEventListener('shown.bs.dropdown', this.onShown);
        }
    }

    // Keep the dropdown open when interacting with the search field.
    stop(event) {
        event.stopPropagation();
    }

    reset() {
        this.searchTarget.value = '';
        this.filter();
    }

    filter() {
        const query = this.searchTarget.value.trim().toLowerCase();

        let anyVisible = false;
        this.itemTargets.forEach((item) => {
            const name = item.dataset.csName || '';
            const matches = query === '' || name.includes(query);
            item.classList.toggle('d-none', !matches);
            if (matches) {
                anyVisible = true;
            }
            this.highlight(item, query);
        });

        // Hide group headers whose entries are all filtered out.
        this.groupTargets.forEach((group) => {
            const groupId = group.textContent.trim();
            const hasVisible = this.itemTargets.some(
                (item) => item.dataset.csGroup === groupId && !item.classList.contains('d-none'),
            );
            group.classList.toggle('d-none', !hasVisible);
        });

        if (this.hasEmptyTarget) {
            this.emptyTarget.classList.toggle('d-none', anyVisible);
        }
    }

    highlight(item, query) {
        const label = item.querySelector('.cs-label');
        if (!label) {
            return;
        }

        const text = label.textContent;
        if (query === '') {
            label.textContent = text;
            return;
        }

        const index = text.toLowerCase().indexOf(query);
        if (index === -1) {
            label.textContent = text;
            return;
        }

        const before = document.createTextNode(text.slice(0, index));
        const mark = document.createElement('mark');
        mark.textContent = text.slice(index, index + query.length);
        const after = document.createTextNode(text.slice(index + query.length));

        label.replaceChildren(before, mark, after);
    }
}
