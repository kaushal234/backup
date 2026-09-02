import { Controller } from '@hotwired/stimulus';

/*
 * Toggles a password field between hidden and visible.
 * Markup:
 *   <input type="password" data-controller="password-toggle" data-password-toggle-target="input">
 *   <button data-action="password-toggle#toggle">…</button>
 * (the button can live anywhere inside the same controller element scope;
 *  here both input and button share the .input-wrap parent — put
 *  data-controller on .input-wrap if you prefer.)
 */
export default class extends Controller {
    static targets = ['input'];

    toggle() {
        const input = this.hasInputTarget
            ? this.inputTarget
            : this.element.querySelector('input');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}
