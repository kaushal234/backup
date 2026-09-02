import { Controller } from "@hotwired/stimulus";
import Routing from "fos-router";
import {
  AutocompleteConnectOptions,
  AutocompletePreConnectOptions,
} from "@symfony/ux-autocomplete";
import { navigate } from "./utils/navigate";

// Use the TomSelect type exposed by ux-autocomplete to avoid the ESM/CJS
// declaration mismatch of the "tom-select" package.
type TomSelectInstance = AutocompleteConnectOptions["tomSelect"];

/**
 * Generic "select then redirect" behaviour for a TomSelect select/autocomplete.
 *
 * Enabled by the `redirect_route` option of SelectFormType. When an option is
 * selected, the destination route is built from:
 *  - routeValue: the Symfony route name to redirect to.
 *  - routeParamsMapValue: a map of "<selected data property>" => "<route param>".
 *    Each property is read on the selected item and injected into the route param.
 *  - routeParamsExtraValue: static extra params merged into the route.
 *
 * The whole selected object is kept by TomSelect (each option stores the full API
 * payload, not only its id), so any property can be mapped to a route parameter.
 *
 * Identifier: `redirect-select`
 */
export default class extends Controller<HTMLSelectElement> {
  static values = {
    route: String,
    routeParamsMap: { type: Object, default: {} },
    routeParamsExtra: { type: Object, default: {} },
  };

  declare readonly routeValue: string;
  declare readonly routeParamsMapValue: Record<string, string>;
  declare readonly routeParamsExtraValue: Record<string, unknown>;

  private tomSelect?: TomSelectInstance;

  initialize(): void {
    this.onPreConnect = this.onPreConnect.bind(this);
    this.onConnect = this.onConnect.bind(this);
  }

  connect(): void {
    this.element.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );
    this.element.addEventListener(
      "autocomplete:connect",
      this.onConnect as EventListener
    );
  }

  disconnect(): void {
    this.element.removeEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );
    this.element.removeEventListener(
      "autocomplete:connect",
      this.onConnect as EventListener
    );
  }

  onConnect(event: CustomEvent<AutocompleteConnectOptions>): void {
    this.tomSelect = event.detail.tomSelect;
  }

  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { options } = event.detail;
    options.onChange = (value: string) => this.redirect(value);
  }

  private redirect(value: string): void {
    if (!value) {
      return;
    }

    const item = (this.tomSelect?.options[value] ?? {}) as Record<
      string,
      unknown
    >;

    const params: Record<string, unknown> = { ...this.routeParamsExtraValue };
    Object.entries(this.routeParamsMapValue).forEach(([property, param]) => {
      params[param] = this.readProperty(item, property) ?? value;
    });

    const route: string = Routing.generate(this.routeValue, params);
    navigate(`/en/private${route}`);
  }

  /**
   * Read a property on the selected item, supporting both a direct key
   * (e.g. "@id") and a dotted path (e.g. "businessUnit.id").
   */
  private readProperty(
    item: Record<string, unknown>,
    property: string
  ): unknown {
    if (property in item) {
      return item[property];
    }

    return property.split(".").reduce<unknown>((acc, key) => {
      if (acc && typeof acc === "object" && key in (acc as object)) {
        return (acc as Record<string, unknown>)[key];
      }

      return undefined;
    }, item);
  }
}
