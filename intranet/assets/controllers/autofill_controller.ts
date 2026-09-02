import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLFormElement> {
  static targets: string[] = ["source"];

  static values = {
    target: String,
  };

  declare targetValue: string;

  declare sourceTarget: HTMLInputElement;

  autofill() {
    const inputs = document.querySelectorAll<HTMLInputElement>(
      `[data-autofill-target="${this.targetValue}"]`
    );
    inputs.forEach((input) => {
      const element = input;
      element.value = this.sourceTarget.value;
    });
  }
}
