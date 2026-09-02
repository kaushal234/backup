import { Controller } from "@hotwired/stimulus";
import Handlebars from "handlebars";
import TomSelect from "tom-select";
import {
  AutocompleteConnectOptions,
  AutocompletePreConnectOptions,
} from "@symfony/ux-autocomplete";
import type { TomOption } from "../types/tomselect";
import { fetch } from "./utils/client";

export async function optionsLoad(
  tomSelect: TomSelect,
  query: string,
  callback: (results?: unknown) => void,
  baseUrl: string,
  dataDisabledValue: string[]
) {
  const url = tomSelect.getUrl(query);

  await fetch(url)
    .then((response) => response.json())
    .then((json) => {
      const items = json["hydra:member"].map((item: any) => {
        const idValue: string = item[tomSelect.settings.valueField];
        const isDisabled = dataDisabledValue.includes(idValue);
        return {
          ...item,
          disabled: isDisabled,
        };
      });

      const nextUri: string | null = json["hydra:view"]["hydra:next"] ?? null;
      if (nextUri) {
        tomSelect.setNextUrl(query, new URL(nextUri, baseUrl));
      }

      callback(items);
    })
    .catch(() => {
      callback();
    });
}

/**
 * Transform an object to a URL parameters string.
 * @param params
 * @param extraQuery
 * @param prefix
 */
export function buildQuery(
  params: URLSearchParams,
  extraQuery: Record<string, unknown>,
  prefix = ""
): void {
  Object.entries(extraQuery).forEach(([key, value]) => {
    const newKey = prefix ? `${prefix}[${key}]` : key;

    if (value === null || value === undefined) return;

    if (Array.isArray(value)) {
      value.forEach((v) => {
        params.append(`${newKey}[]`, String(v));
      });

      return;
    }

    if (typeof value === "object") {
      buildQuery(params, value as Record<string, unknown>, newKey);

      return;
    }

    params.append(newKey, String(value));
  });
}

export default class extends Controller<HTMLFormElement> {
  static values = {
    itemsPerPage: Number,
    page: Number,
    extraQuery: Object,
    templateSelection: String,
    templateResult: String,
    allowClear: {
      type: Boolean,
      default: true,
    },
    dataDisabled: {
      type: Array,
      default: [],
    },
    searchParamName: String,
    searchWildcard: String,
  };

  declare readonly hasItemsPerPageValue: boolean;
  declare readonly itemsPerPageValue: number;
  declare readonly hasPageValue: boolean;
  declare readonly pageValue: number;
  declare readonly hasExtraQueryValue: boolean;
  declare readonly extraQueryValue: Record<string, unknown>;
  declare readonly hasTemplateSelectionValue: boolean;
  declare readonly templateSelectionValue: string;
  declare readonly hasTemplateResultValue: boolean;
  declare readonly templateResultValue: string;
  declare readonly allowClearValue: boolean;
  declare readonly dataDisabledValue: string[];
  declare readonly hasSearchParamNameValue: boolean;
  declare readonly searchParamNameValue: string;
  declare readonly hasSearchWildcardValue: boolean;
  declare readonly searchWildcardValue: string;

  private baseUrl?: string = process.env.WEBPACK_API_URI;

  private ajaxUri: string | null = null;

  initialize(): void {
    this.onPreConnect = this.onPreConnect.bind(this);
    this.onConnect = this.onConnect.bind(this);
    this.optionsRenderOption = this.optionsRenderOption.bind(this);
    this.optionsRenderItem = this.optionsRenderItem.bind(this);

    this.ajaxUri = this.element.getAttribute(
      "data-symfony--ux-autocomplete--autocomplete-url-value"
    );
  }

  connect(): void {
    if (!this.baseUrl) {
      throw new Error("WEBPACK_API_URI is not defined");
    }
    this.element.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );
    this.element.addEventListener(
      "autocomplete:connect",
      this.onConnect as EventListener
    );
  }

  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { ajaxUri, baseUrl, dataDisabledValue } = this;
    const { options } = event.detail;

    options.clearAfterSelect = true;
    options.hideSelected = true;
    options.firstUrl = (query: string) => {
      const url: URL = new URL(ajaxUri as string, baseUrl);
      const params = new URLSearchParams();

      let searchParamName = "q";
      if (this.hasSearchParamNameValue) {
        searchParamName = this.searchParamNameValue;
      }

      let queryValue = query;
      if (this.hasSearchWildcardValue) {
        queryValue =
          this.searchWildcardValue + queryValue + this.searchWildcardValue;
      }

      if (query) {
        params.append(searchParamName, queryValue);
      }

      if (this.hasItemsPerPageValue) {
        params.append("itemsPerPage", this.itemsPerPageValue.toString());
      }

      if (this.hasPageValue) {
        params.append("page", this.pageValue.toString());
      }

      if (this.hasExtraQueryValue) {
        buildQuery(params, this.extraQueryValue);
      }

      url.search = params.toString();

      return url.toString();
    };
    options.load = async function tomSelectLoad(
      query: string,
      callback: () => void
    ) {
      await optionsLoad(
        this,
        query,
        callback,
        baseUrl as string,
        dataDisabledValue
      );
    };
    if (this.hasTemplateSelectionValue || this.hasTemplateResultValue) {
      options.render.option = this.optionsRenderOption;
    }

    if (this.hasTemplateSelectionValue) {
      options.render.item = this.optionsRenderItem;
    }

    if (!this.allowClearValue) {
      delete options.plugins.clear_button;
    }

    options.render.no_results = () => {
      return `<div class="no-results">No results found</div>`;
    };
  }

  onConnect(event: CustomEvent<AutocompleteConnectOptions>): void {
    const { tomSelect } = event.detail;
    Object.entries(tomSelect.options).forEach(([key, value]) => {
      const tomOption: TomOption = value as TomOption;
      if (tomOption.nameUnresolved) {
        const url = new URL(key, this.baseUrl);
        fetch(url)
          .then((response) => response.json())
          .then((json) => {
            tomSelect.updateOption(key, { ...tomOption, ...json });
          });
      }
    });
  }

  optionsRenderOption(item: Record<string, unknown>): string {
    if (item.text) {
      return `<div>${item.text}</div>`;
    }

    let template = this.templateSelectionValue;
    if (this.hasTemplateResultValue) {
      template = this.templateResultValue;
    }

    const renderWithValues = Handlebars.compile(template);

    return `<div>${renderWithValues(item)}</div>`;
  }

  optionsRenderItem(item: Record<string, unknown>): string {
    if (item.text) {
      return `<div>${item.text}</div>`;
    }

    const renderWithValues = Handlebars.compile(this.templateSelectionValue);

    return `<div>${renderWithValues(item)}</div>`;
  }
}
