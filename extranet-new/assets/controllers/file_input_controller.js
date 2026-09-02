import { Controller } from '@hotwired/stimulus';

/*
 * Replaces the browser-native file input UI (whose "Choose file" / "No file
 * chosen" labels are rendered in the browser's own locale, regardless of the
 * page language) with a custom, fully translatable button + filename display.
 * Markup:
 *   <div data-controller="file-input" data-file-input-empty-value="No file chosen">
 *     <input type="file" class="d-none"
 *            data-file-input-target="input" data-action="file-input#update">
 *     <button type="button" data-action="file-input#browse">Choose file</button>
 *     <span data-file-input-target="filename">No file chosen</span>
 *   </div>
 */
export default class extends Controller {
    static targets = ['input', 'filename'];
    static values = { empty: String };

    browse() {
        this.inputTarget.click();
    }

    update() {
        const { files } = this.inputTarget;
        this.filenameTarget.textContent = files.length > 0 ? files[0].name : this.emptyValue;
    }
}