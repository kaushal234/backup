import { Controller } from "@hotwired/stimulus";
import { renderMarkdown } from "./utils/render_markdown";

// Stimulus controller for an external AI service certification modal (Copilot, DeepL, ...).
// Renders the markdown message, gates the confirm button behind the certify checkbox,
// opens the service in a new tab, and resets the modal once it is hidden.
export default class extends Controller {
  static targets = ["message", "certify", "confirm"];

  static values = {
    url: String,
  };

  declare readonly messageTarget: HTMLElement;

  declare readonly certifyTarget: HTMLInputElement;

  declare readonly confirmTarget: HTMLButtonElement;

  declare readonly urlValue: string;

  private readonly reset = (): void => {
    this.certifyTarget.checked = false;
    this.confirmTarget.disabled = true;
  };

  connect(): void {
    this.messageTarget.innerHTML = renderMarkdown(
      this.messageTarget.dataset.message ?? ""
    );
    this.element.addEventListener("hidden.bs.modal", this.reset);
  }

  disconnect(): void {
    this.element.removeEventListener("hidden.bs.modal", this.reset);
  }

  toggle(): void {
    this.confirmTarget.disabled = !this.certifyTarget.checked;
  }

  open(): void {
    window.open(this.urlValue, "_blank", "noopener,noreferrer");
  }
}
