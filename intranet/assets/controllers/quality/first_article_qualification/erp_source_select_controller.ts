import { Controller } from "@hotwired/stimulus";
import { AutocompletePreConnectOptions } from "@symfony/ux-autocomplete";
import { fetch } from "../../utils/client";

/**
 * Event dispatched on `window` every time the known ERP code changes:
 * when a Factory is selected, cleared, or replaced by another one.
 * Other controllers (e.g. part_number_line_controller) listen to this
 * to filter their own search by "site" (the ERP code).
 */
export const ERP_CHANGED_EVENT = "erp-source:change";

/**
 * Reads the ERP code currently known, stored on the <html> element so it
 * survives across controller instances (e.g. Part Number lines added
 * dynamically after the Factory was already selected).
 */
export function getCurrentErp(): string | null {
  return document.documentElement.dataset.currentErp ?? null;
}

/**
 * Stores the ERP code (or clears it) and notifies every listener via
 * ERP_CHANGED_EVENT.
 */
function setCurrentErp(erp: string | null): void {
  if (erp) {
    document.documentElement.dataset.currentErp = erp;
  } else {
    delete document.documentElement.dataset.currentErp;
  }

  window.dispatchEvent(new CustomEvent(ERP_CHANGED_EVENT, { detail: { erp } }));
}

/**
 * Controller placed on the Factory autocomplete select.
 *
 * TomSelect's search results only contain what we explicitly display
 * (the "template" option), not the full Location object — so to get the
 * ERP code, we re-fetch the selected Factory by its IRI once chosen.
 */
export default class extends Controller<HTMLElement> {
  private baseUrl?: string = process.env.WEBPACK_API_URI;

  initialize(): void {
    this.onPreConnect = this.onPreConnect.bind(this);
  }

  connect(): void {
    if (!this.baseUrl) {
      throw new Error("baseUrl is not defined");
    }

    this.element.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );

    // If the Factory already has a value on connect (e.g. edit mode, or
    // pre-filled via the "erp" query param), resolve its ERP right away
    // instead of waiting for the user to reselect it.
    const select = this.element as HTMLSelectElement;
    if (select.value) {
      this.fetchAndSetErp(select.value);
    }
  }

  /**
   * Called once, right before TomSelect is built on the Factory field.
   * Lets us plug into item selection/clearing to resolve and broadcast
   * the ERP code.
   */
  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { options } = event.detail;

    options.onItemAdd = (value: string) => this.fetchAndSetErp(value);
    options.onClear = (): void => setCurrentErp(null);
  }

  /**
   * Fetches the full Location resource by its IRI to read its "erp"
   * property, then stores/broadcasts it.
   */
  private fetchAndSetErp(value: string): Promise<void> {
    const url = new URL(value, this.baseUrl);
    return fetch(url)
      .then((response: Response) => response.json())
      .then((json: { erp?: string }) => setCurrentErp(json.erp ?? null))
      .catch((error: unknown) => {
        console.error("Failed to fetch factory element.", { error, id: value });
      });
  }
}
