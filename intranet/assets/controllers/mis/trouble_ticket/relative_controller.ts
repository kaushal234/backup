import { Controller } from "@hotwired/stimulus";

/**
 * Loads the "Similar tickets" panel once a module + option are both selected, and handles the
 * reaction buttons (POST -> reload). The counts/states are re-rendered server-side, so a click
 * just re-fetches the panel. Attach to the #tts-relative container.
 *
 * The module/option fields live in a different column, so they are located by name. Their values
 * are polled (rather than listening for change) because the TomSelect autocomplete does not emit a
 * reliable native change event.
 */
export default class extends Controller<HTMLElement> {
  static values = {
    url: String,
  };

  declare readonly urlValue: string;

  private moduleField: HTMLInputElement | HTMLSelectElement | null = null;

  private optionField: HTMLInputElement | HTMLSelectElement | null = null;

  private last = "";

  private pollId = 0;

  connect(): void {
    this.moduleField = document.querySelector(
      '[name="trouble_ticket[module]"]'
    );
    this.optionField = document.querySelector('[name="trouble_ticket[type]"]');
    if (!this.moduleField || !this.optionField) {
      return;
    }

    this.pollId = window.setInterval(() => this.load(false), 600);
    this.load(true);

    this.element.addEventListener("click", this.onReactionClick);
  }

  disconnect(): void {
    window.clearInterval(this.pollId);
    this.element.removeEventListener("click", this.onReactionClick);
  }

  private load(force: boolean): void {
    const moduleValue = this.moduleField?.value || "";
    const optionValue = this.optionField?.value || "";
    const key = `${moduleValue}|${optionValue}`;
    if (!force && key === this.last) {
      return;
    }
    this.last = key;

    if (!moduleValue || !optionValue) {
      this.element.innerHTML = "";
      return;
    }

    const query = `?type=${encodeURIComponent(
      optionValue
    )}&module=${encodeURIComponent(moduleValue)}`;

    fetch(this.urlValue + query, {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .then((response) => (response.ok ? response.text() : ""))
      .then((html) => {
        this.element.innerHTML = html;
      })
      .catch(() => {
        this.element.innerHTML = "";
      });
  }

  private onReactionClick = (event: Event): void => {
    const target = event.target as HTMLElement;
    const button = target.closest<HTMLButtonElement>("[data-action-url]");
    if (!button || button.disabled) {
      return;
    }
    event.preventDefault();
    button.disabled = true;

    const actionUrl = button.getAttribute("data-action-url");
    if (!actionUrl) {
      button.disabled = false;
      return;
    }

    fetch(actionUrl, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .then(() => this.load(true))
      .catch(() => {
        button.disabled = false;
      });
  };
}
