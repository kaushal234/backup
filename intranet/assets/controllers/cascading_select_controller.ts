import { Controller } from "@hotwired/stimulus";
import { AutocompletePreConnectOptions } from "@symfony/ux-autocomplete";
import TomSelect from "tom-select";
import { fetch } from "./utils/client";
import type { TomSelectElement } from "../types/tomselect";

/**
 * This controller manipulates two selects forms using TomSelect autocomplete
 * to get value from the first one and filter to the second one.
 */
export default class extends Controller<HTMLFormElement> {
  static values = {
    targetForm: String,
    fromProperty: String,
    toFilter: String,
  };

  declare targetFormValue: string;

  declare toFilterValue: string;

  declare fromPropertyValue: string;

  private baseUrl?: string = process.env.WEBPACK_API_URI;

  initialize(): void {
    this.onPreConnect = this.onPreConnect.bind(this);
  }

  connect(): void {
    if (this.targetElement.value === "") {
      this.targetElement.disabled = true;
    }
    if (!this.baseUrl) {
      throw new Error("baseUrl is not defined");
    }

    this.element.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );
  }

  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { options } = event.detail;
    const { baseUrl, targetElement, toFilterValue, fromPropertyValue } = this;

    options.onItemAdd = (value: string) => {
      const url = new URL(value, baseUrl);
      return fetch(url)
        .then((response) => response.json())
        .then((json) => {
          const targetSelect: TomSelect = targetElement.tomselect as TomSelect;
          const extraQuery = JSON.parse(
            targetElement.dataset.autocompleteExtraQueryValue || "{}"
          );
          extraQuery[toFilterValue] = json[fromPropertyValue];
          targetElement.dataset.autocompleteExtraQueryValue =
            JSON.stringify(extraQuery);

          targetSelect.clear();
          targetSelect.clearOptions();
          targetSelect.clearCache();

          targetSelect.on("focus", () => {
            if (!targetSelect.lastQuery) {
              targetSelect.load("");
            }
          });

          targetSelect.enable();
        })
        .catch((error) => {
          console.error("Failed to fetch target element.", {
            error,
            id: value,
          });
        });
    };

    options.onClear = () => {
      const targetSelect: TomSelect = targetElement.tomselect as TomSelect;
      targetSelect.disable();
    };
  }

  get targetElementId() {
    return `#${this.targetFormValue}`;
  }

  get targetElement() {
    const element = document.querySelector(
      this.targetElementId
    ) as TomSelectElement;
    if (element === null) {
      throw new Error(`Element ${this.targetElementId} not found.`);
    }
    return element;
  }
}
