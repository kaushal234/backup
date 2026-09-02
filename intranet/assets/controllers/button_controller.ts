import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["submit"];

  declare readonly submitTarget: HTMLButtonElement;

  disable(): void {
    this.submitTarget.disabled = true;
  }
}
