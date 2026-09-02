import { Controller } from '@hotwired/stimulus';

/*
 * locale-switcher — submits the chosen locale to the switch route and reloads.
 *
 * Lives on the footer language <select>. Wired as a Stimulus controller so the
 * change handler is re-attached on every Turbo navigation (a plain
 * $(document).ready handler is lost when Turbo swaps the <body>).
 *
 * Usage:
 *   <select data-controller="locale-switcher"
 *           data-locale-switcher-current-value="{{ current_locale }}"
 *           data-locale-switcher-action-value="{{ path('locale:switch') }}"
 *           data-action="change->locale-switcher#switch">
 *     <option value="fr">Français</option>
 *   </select>
 */
export default class extends Controller {
    static values = { current: String, action: String };

    switch(event) {
        const value = event.target.value;
        if (value === this.currentValue) {
            return;
        }

        const data = new FormData();
        data.append('locale', value);

        fetch(this.actionValue, { method: 'POST', body: data }).then(() => {
            document.location.reload();
        });
    }
}
