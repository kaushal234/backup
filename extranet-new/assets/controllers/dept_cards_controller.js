import { Controller } from '@hotwired/stimulus';

/*
 * dept-cards — turns the real <select name="contact[department]"> into a set of
 * clickable cards, while keeping the native select as the source of truth so the
 * Symfony form binding is unchanged. If JS is disabled, the native select shows
 * and works on its own.
 *
 * Markup (see contact/index.html.twig):
 *   <div data-controller="dept-cards">
 *     <div class="cf-native-select">{{ select with data-dept-cards-target="select" data-action="dept-cards#sync" }}</div>
 *     <div data-dept-cards-target="cards">
 *       <button data-dept-cards-target="card" data-value="Sales"  data-action="dept-cards#pick">…</button>
 *       …
 *     </div>
 *   </div>
 */
export default class extends Controller {
    static targets = ['select', 'cards', 'card'];

    connect() {
        // JS available → hide the native select, drive via cards.
        if (this.hasSelectTarget) {
            this.selectTarget.closest('.cf-native-select').style.display = 'none';
        }
        this.render();
    }

    pick(event) {
        const value = event.currentTarget.dataset.value;
        if (this.hasSelectTarget) this.selectTarget.value = value;
        this.render();
    }

    sync() { this.render(); } // native select changed (no-JS fallback path)

    render() {
        const value = this.hasSelectTarget ? this.selectTarget.value : null;
        this.cardTargets.forEach((c) =>
            c.classList.toggle('active', c.dataset.value === value));
    }
}
