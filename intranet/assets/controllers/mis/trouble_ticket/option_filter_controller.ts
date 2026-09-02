import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLFormElement> {
  static targets = ["category", "option", "help", "message", "indice"];

  static values = {
    incidentHelp: String,
    requestHelp: String,
    notifyMessage: String,
    purchaseMessage: String,
  };

  declare readonly hasCategoryTarget: boolean;

  declare readonly categoryTarget: HTMLSelectElement;

  declare readonly hasOptionTarget: boolean;

  declare readonly optionTarget: HTMLSelectElement;

  declare readonly hasHelpTarget: boolean;

  declare readonly helpTarget: HTMLElement;

  declare readonly hasMessageTarget: boolean;

  declare readonly messageTarget: HTMLElement;

  /** Hidden severity ("iFactor") input, derived from the selected option's data-indice. */
  declare readonly hasIndiceTarget: boolean;

  declare readonly indiceTarget: HTMLInputElement;

  declare readonly incidentHelpValue: string;

  declare readonly requestHelpValue: string;

  declare readonly notifyMessageValue: string;

  declare readonly purchaseMessageValue: string;

  /** The backdrop element while the popup is open (also our "is open" flag). */
  private backdrop: HTMLElement | null = null;

  connect(): void {
    if (!this.hasCategoryTarget || !this.hasOptionTarget) {
      return;
    }

    // Reflect a preselected option's category, then filter once.
    const pre = this.optionTarget.options[this.optionTarget.selectedIndex];
    if (pre && pre.value) {
      const category = this.categoryOf(pre);
      if (category) {
        this.categoryTarget.value = category;
      }
    }
    this.apply();
    // Reflect the (possibly preselected) option's severity into the hidden indiceFactor field.
    this.syncIndice();
  }

  disconnect(): void {
    // Tidy up if we navigate away (Turbo) while the popup is open.
    this.closeInfo();
  }

  /** data-action on the category select. */
  apply(): void {
    if (!this.hasCategoryTarget || !this.hasOptionTarget) {
      return;
    }
    const { value } = this.categoryTarget;

    Array.from(this.optionTarget.options).forEach((option) => {
      if (!option.value) {
        return;
      }
      // Type-first: with no category chosen, no real option is selectable.
      const match = Boolean(value) && this.categoryOf(option) === value;
      const opt = option;
      opt.hidden = !match;
      opt.disabled = !match;
    });

    const current = this.optionTarget.options[this.optionTarget.selectedIndex];
    if (
      current &&
      current.value &&
      (!value || this.categoryOf(current) !== value)
    ) {
      this.optionTarget.value = "";
    }

    if (this.hasHelpTarget) {
      this.helpTarget.textContent = this.helpFor(value);
    }

    // The selected option may have just been cleared by the category filter; keep severity in sync.
    this.syncIndice();
  }

  /** data-action on the option select. */
  onOptionChange(): void {
    if (!this.hasOptionTarget) {
      return;
    }
    const option = this.optionTarget.options[this.optionTarget.selectedIndex];
    if (!option) {
      return;
    }

    // Derive the severity ("iFactor") from the chosen option and store it for submission.
    this.syncIndice();

    // Type-first: picking an option no longer back-fills the category (no reverse sync).
    // Informational popups, mirroring the React form (notify CIOs / purchase request).
    if (option.getAttribute("data-notify") === "1") {
      this.showInfo(this.notifyMessageValue);
    } else if (option.getAttribute("data-purchase-warning") === "1") {
      this.showInfo(this.purchaseMessageValue);
    }
  }

  /**
   * Mirror the selected option's data-indice into the hidden indiceFactor field so the severity is
   * submitted and stored on create — with no visible field, as the original form did. When there is
   * no severity (placeholder / an option without one), disable the input so it is omitted from the
   * POST entirely and the API stores null, rather than an empty string that would fail validation.
   */
  private syncIndice(): void {
    if (!this.hasIndiceTarget || !this.hasOptionTarget) {
      return;
    }
    const option = this.optionTarget.options[this.optionTarget.selectedIndex];
    const indice = option ? this.indiceOf(option) : "";

    if (indice) {
      this.indiceTarget.value = indice;
      this.indiceTarget.disabled = false;
    } else {
      this.indiceTarget.value = "";
      this.indiceTarget.disabled = true;
    }
  }

  private indiceOf(option: HTMLOptionElement): string {
    return option.getAttribute("data-indice") || "";
  }

  private categoryOf(option: HTMLOptionElement): string {
    return option.getAttribute("data-category") || "";
  }

  private helpFor(category: string): string {
    if (category === "Incident") {
      return this.incidentHelpValue;
    }
    if (category === "Request") {
      return this.requestHelpValue;
    }
    return "";
  }

  private showInfo(message: string): void {
    if (!this.hasMessageTarget) {
      return;
    }
    this.messageTarget.textContent = message;

    const modal = this.messageTarget.closest<HTMLElement>(".modal");
    if (!modal || this.backdrop) {
      return;
    }

    modal.style.display = "block";
    modal.classList.add("show");
    modal.removeAttribute("aria-hidden");
    modal.setAttribute("aria-modal", "true");
    modal.addEventListener("click", this.handleDismiss);

    this.backdrop = document.createElement("div");
    this.backdrop.className = "modal-backdrop fade show";
    document.body.appendChild(this.backdrop);
    document.body.classList.add("modal-open");
    document.addEventListener("keydown", this.handleKeydown);
  }

  private closeInfo(): void {
    const modal = this.hasMessageTarget
      ? this.messageTarget.closest<HTMLElement>(".modal")
      : null;

    if (modal) {
      modal.classList.remove("show");
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");
      modal.removeAttribute("aria-modal");
      modal.removeEventListener("click", this.handleDismiss);
    }

    this.backdrop?.remove();
    this.backdrop = null;
    document.body.classList.remove("modal-open");
    document.removeEventListener("keydown", this.handleKeydown);
  }

  /** Close on a dismiss control, the component's close button, or a click outside the dialog. */
  private readonly handleDismiss = (event: MouseEvent): void => {
    const target = event.target as HTMLElement;
    if (
      target === event.currentTarget ||
      target.closest('[data-bs-dismiss="modal"]')
    ) {
      this.closeInfo();
    }
  };

  private readonly handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape") {
      this.closeInfo();
    }
  };
}
