import { Controller } from "@hotwired/stimulus";
import TomSelect from "tom-select";
import type { TomSelectElement } from "../../../types/tomselect";

type ApplicationRef = { "@id"?: string; name?: string } | string;

type TomOptionWithApplication = {
  application?: ApplicationRef;
} & Record<string, unknown>;

type TomSelectInternals = {
  loadedSearches: Record<string, unknown>;
  lastQuery: string | null;
  isOpen?: boolean;
  clearPagination?: () => void;
};

export default class extends Controller<HTMLFormElement> {
  private appEl: TomSelectElement | null = null;

  private modEl: TomSelectElement | null = null;

  private syncingApplication = false;

  private applyingApplicationFilter = false;

  connect(): void {
    const appEl = this.element.querySelector<TomSelectElement>(
      "#trouble_ticket_application"
    );
    const modEl = this.element.querySelector<TomSelectElement>(
      "#trouble_ticket_module"
    );
    if (!appEl || !modEl) {
      return;
    }
    this.appEl = appEl;
    this.modEl = modEl;

    this.whenTom(appEl, (appTom) => {
      appTom.on("change", this.onApplicationChange);
    });

    this.whenTom(modEl, (modTom) => {
      modTom.on("focus", this.onModuleFocus);
      modTom.on("change", this.onModuleChange);

      const initial = modTom.getValue();
      if (typeof initial === "string" && initial) {
        this.syncInitial(modTom, initial, 0);
      }
    });
  }

  disconnect(): void {
    this.appEl?.tomselect?.off("change", this.onApplicationChange);
    this.modEl?.tomselect?.off("focus", this.onModuleFocus);
    this.modEl?.tomselect?.off("change", this.onModuleChange);
  }

  private whenTom(
    el: TomSelectElement,
    cb: (ts: TomSelect) => void,
    tries = 0
  ): void {
    if (el.tomselect) {
      cb(el.tomselect);
      return;
    }
    if (tries > 100) {
      return;
    }
    window.setTimeout(() => this.whenTom(el, cb, tries + 1), 100);
  }

  private syncInitial(modTom: TomSelect, initial: string, tries: number): void {
    const data = modTom.options[initial] as
      | TomOptionWithApplication
      | undefined;
    if (this.appEl?.tomselect && data && data.application) {
      this.syncApplicationFromModule(initial);
      return;
    }
    if (tries > 80) {
      return;
    }
    window.setTimeout(() => this.syncInitial(modTom, initial, tries + 1), 100);
  }

  private onApplicationChange = (value?: string): void => {
    if (this.syncingApplication) {
      return;
    }
    const { appEl } = this;
    if (!appEl) {
      return;
    }
    const appTom = appEl.tomselect;
    const appValue =
      (typeof value === "string" && value) ||
      (appTom ? (appTom.getValue() as string) : appEl.value) ||
      "";

    this.applyApplicationFilter(appValue);
  };

  private onModuleChange = (value: string): void => {
    if (this.applyingApplicationFilter) {
      return;
    }
    if (value) {
      this.syncApplicationFromModule(value);
    }
    this.scheduleModuleListReset();
  };

  private onModuleFocus = (): void => {
    const modTom = this.modEl?.tomselect;
    if (modTom && !(modTom as unknown as TomSelectInternals).lastQuery) {
      modTom.load("");
    }
  };

  private applyApplicationFilter(appValue: string): void {
    const { modEl } = this;
    if (!modEl) {
      return;
    }
    const modTom = modEl.tomselect;
    if (!modTom) {
      return;
    }

    this.writeApplicationQuery(modEl, appValue);

    this.applyingApplicationFilter = true;
    modTom.clear(true);
    this.resetModuleList(modTom);
    this.applyingApplicationFilter = false;

    if ((modTom as unknown as TomSelectInternals).isOpen) {
      modTom.load("");
    }
  }

  private scheduleModuleListReset(): void {
    window.setTimeout(() => {
      const modTom = this.modEl?.tomselect;
      if (!modTom) {
        return;
      }
      this.resetModuleList(modTom);
      if ((modTom as unknown as TomSelectInternals).isOpen) {
        modTom.load("");
      }
    }, 0);
  }

  private resetModuleList(modTom: TomSelect): void {
    const internals = modTom as unknown as TomSelectInternals;
    modTom.clearOptions();
    internals.loadedSearches = {};
    internals.lastQuery = null;
    if (typeof internals.clearPagination === "function") {
      internals.clearPagination();
    }
  }

  private syncApplicationFromModule = (value: string): void => {
    const { appEl, modEl } = this;
    if (!appEl || !modEl || !value) {
      return;
    }
    const modTom = modEl.tomselect;
    if (!modTom) {
      return;
    }
    const data = modTom.options[value] as TomOptionWithApplication | undefined;
    if (!data || !data.application) {
      return;
    }
    const { application } = data;
    const appIri =
      typeof application === "string" ? application : application["@id"];
    if (!appIri) {
      return;
    }
    const appName =
      typeof application === "string" ? appIri : application.name || appIri;

    const appTom = appEl.tomselect;
    if (appTom) {
      if (appTom.getValue() !== appIri) {
        this.syncingApplication = true;
        appTom.addOption({ "@id": appIri, name: appName });
        appTom.setValue(appIri, true);
        this.syncingApplication = false;
      }
    } else if (appEl.value !== appIri) {
      appEl.value = appIri;
    }

    this.writeApplicationQuery(modEl, appIri);
  };

  private writeApplicationQuery(
    modEl: TomSelectElement,
    appValue: string
  ): void {
    const extra = this.readExtraQuery(modEl);
    if (appValue) {
      extra.application = appValue;
    } else {
      delete extra.application;
    }
    modEl.setAttribute(
      "data-autocomplete-extra-query-value",
      JSON.stringify(extra)
    );
  }

  private readExtraQuery(modEl: TomSelectElement): Record<string, unknown> {
    try {
      return JSON.parse(
        modEl.dataset.autocompleteExtraQueryValue || "{}"
      ) as Record<string, unknown>;
    } catch {
      return {};
    }
  }
}
