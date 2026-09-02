import { Controller } from "@hotwired/stimulus";
import { AutocompletePreConnectOptions } from "@symfony/ux-autocomplete";
import TomSelect from "tom-select";
import { fetch } from "../../utils/client";
import type { TomSelectElement } from "../../../types/tomselect";
import {
  ERP_CHANGED_EVENT,
  getCurrentErp,
} from "./erp_source_select_controller";

/**
 * Controller for a single "Part Number" line of the FAQ form.
 *
 * It has two responsibilities:
 * 1. Keep the Part Number autocomplete filtered by the Factory's ERP code
 *    (received from erp_source_select_controller via the ERP_CHANGED_EVENT),
 *    enabling/disabling the field depending on whether an ERP is known.
 * 2. Auto-fill the Revision and Description fields of this same line when
 *    the user picks an item in the Part Number autocomplete.
 */
export default class extends Controller<HTMLElement> {
  static targets = ["number", "revision", "description"];

  declare readonly numberTarget: TomSelectElement;
  declare readonly revisionTarget: HTMLInputElement;
  declare readonly descriptionTarget: HTMLInputElement;

  private baseUrl?: string = process.env.WEBPACK_API_URI;

  initialize(): void {
    // Bind once so the same function reference can be added/removed as an event listener.
    this.onPreConnect = this.onPreConnect.bind(this);
    this.onErpChanged = this.onErpChanged.bind(this);
  }

  connect(): void {
    if (!this.baseUrl) {
      throw new Error("baseUrl is not defined");
    }

    // Hook into TomSelect's setup on the Part Number field (see onPreConnect below).
    this.numberTarget.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );

    // React whenever the Factory's ERP changes (selected, cleared, or changed later).
    window.addEventListener(
      ERP_CHANGED_EVENT,
      this.onErpChanged as EventListener
    );

    // On connect (e.g. page load, or a line added dynamically after the Factory
    // was already selected), immediately apply whatever ERP is already known.
    this.applyErp(getCurrentErp());
  }

  disconnect(): void {
    window.removeEventListener(
      ERP_CHANGED_EVENT,
      this.onErpChanged as EventListener
    );
  }

  onErpChanged(event: CustomEvent<{ erp: string | null }>): void {
    this.applyErp(event.detail.erp);
  }

  /**
   * Updates the "site" filter used by the Part Number search, and
   * enables/disables the field depending on whether an ERP is known.
   */
  private applyErp(erp: string | null): void {
    const extraQuery = JSON.parse(
      this.numberTarget.dataset.autocompleteExtraQueryValue || "{}"
    );
    extraQuery.site = erp ?? undefined;
    this.numberTarget.dataset.autocompleteExtraQueryValue =
      JSON.stringify(extraQuery);

    if (this.numberTarget.value !== "") {
      return;
    }

    const targetSelect = this.numberTarget.tomselect as TomSelect | undefined;
    this.numberTarget.disabled = !erp;

    if (erp) {
      targetSelect?.enable();
    } else {
      targetSelect?.disable();
    }
  }

  /**
   * Called once, right before TomSelect is built on the Part Number field.
   * Lets us plug into the item selection to auto-fill Revision/Description
   * using the data already returned by the API for that item — no extra
   * request beyond what TomSelect itself needs to resolve the selection.
   */
  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { options } = event.detail;
    const { baseUrl, revisionTarget, descriptionTarget } = this;

    options.onItemAdd = (value: string) => {
      const url = new URL(value, baseUrl);
      return fetch(url)
        .then((response: Response) => response.json())
        .then((json: { revision?: string; itemDescription?: string }) => {
          revisionTarget.value = json.revision ?? "";
          descriptionTarget.value = json.itemDescription ?? "";
        })
        .catch((error: unknown) => {
          console.error("Failed to fetch item element.", { error, id: value });
        });
    };
  }
}
