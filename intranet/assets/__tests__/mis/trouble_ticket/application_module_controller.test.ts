import {
  describe,
  expect,
  it,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import ApplicationModuleController from "../../../controllers/mis/trouble_ticket/application_module_controller";
import type { TomSelectElement } from "../../../types/tomselect";

const HTML = `
  <form data-controller="mis--trouble-ticket--application-module">
    <select id="trouble_ticket_application"></select>
    <select id="trouble_ticket_module"></select>
  </form>`;

const register = (): Application => {
  const application = Application.start();
  application.register(
    "mis--trouble-ticket--application-module",
    ApplicationModuleController
  );
  return application;
};

describe("ApplicationModuleController", () => {
  let application: Application;

  afterEach(() => {
    application?.stop();
    jest.clearAllMocks();
    jest.useRealTimers();
  });

  it("does nothing when one of the selects is missing", async () => {
    document.body.innerHTML = `
      <form data-controller="mis--trouble-ticket--application-module">
        <select id="trouble_ticket_application"></select>
      </form>`;
    const appEl = document.querySelector(
      "#trouble_ticket_application"
    ) as TomSelectElement;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const appTom: any = { on: jest.fn() };
    appEl.tomselect = appTom;

    application = register();
    await Promise.resolve();

    expect(appTom.on).not.toHaveBeenCalled();
  });

  describe("with both selects", () => {
    let appEl: TomSelectElement;
    let modEl: TomSelectElement;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let appTom: any;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let modTom: any;

    const extraQuery = (): Record<string, unknown> => {
      try {
        return JSON.parse(modEl.dataset.autocompleteExtraQueryValue || "{}");
      } catch {
        return {};
      }
    };

    const moduleHandler = (
      event: string
    ): ((value?: string) => void) | undefined => {
      const call = modTom.on.mock.calls.find(
        (entry: unknown[]) => entry[0] === event
      );
      return call?.[1] as ((value?: string) => void) | undefined;
    };

    const applicationHandler = (): ((value?: string) => void) | undefined => {
      const call = appTom.on.mock.calls.find(
        (entry: unknown[]) => entry[0] === "change"
      );
      return call?.[1] as ((value?: string) => void) | undefined;
    };

    const setup = async (
      moduleValue = "",
      moduleOptions: Record<string, unknown> = {}
    ): Promise<void> => {
      document.body.innerHTML = HTML;
      appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      appTom = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        addOption: jest.fn(),
        setValue: jest.fn(),
      };
      modTom = {
        on: jest.fn(),
        getValue: jest.fn(() => moduleValue),
        options: moduleOptions,
        clear: jest.fn(),
        clearOptions: jest.fn(),
        clearPagination: jest.fn(),
        load: jest.fn(),
        isOpen: false,
        lastQuery: null,
        loadedSearches: {},
      };
      appEl.tomselect = appTom;
      modEl.tomselect = modTom;
      application = register();
      await Promise.resolve();
    };

    it("writes the application filter and resets the module list", async () => {
      await setup();
      appTom.getValue.mockReturnValue("/applications/5");
      applicationHandler()?.("/applications/5");

      expect(extraQuery().application).toBe("/applications/5");
      expect(modTom.clear).toHaveBeenCalledWith(true);
      expect(modTom.clearOptions).toHaveBeenCalled();
      expect(modTom.clearPagination).toHaveBeenCalled();
      expect(modTom.lastQuery).toBeNull();
    });

    it("reloads the open module list immediately after an application change", async () => {
      await setup();
      modTom.isOpen = true;
      appTom.getValue.mockReturnValue("/applications/5");
      applicationHandler()?.("/applications/5");

      expect(modTom.load).toHaveBeenCalledWith("");
    });

    it("does not reload a closed module list on application change", async () => {
      await setup();
      modTom.isOpen = false;
      appTom.getValue.mockReturnValue("/applications/5");
      applicationHandler()?.("/applications/5");

      expect(modTom.load).not.toHaveBeenCalled();
    });

    it("removes the filter when the application is cleared", async () => {
      await setup();
      appTom.getValue.mockReturnValue("/applications/5");
      applicationHandler()?.("/applications/5");
      appTom.getValue.mockReturnValue("");
      applicationHandler()?.("");

      expect(extraQuery().application).toBeUndefined();
    });

    it("recovers from an invalid extra-query attribute", async () => {
      await setup();
      modEl.dataset.autocompleteExtraQueryValue = "not json";
      appTom.getValue.mockReturnValue("/applications/5");
      applicationHandler()?.("/applications/5");

      expect(extraQuery().application).toBe("/applications/5");
    });

    it("does nothing when the module tomselect is gone", async () => {
      await setup();
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (modEl as any).tomselect = undefined;
      appTom.getValue.mockReturnValue("/applications/5");

      expect(() => applicationHandler()?.("/applications/5")).not.toThrow();
    });

    it("loads the module list on focus when nothing was queried", async () => {
      await setup();
      const onFocus = moduleHandler("focus");
      modTom.lastQuery = null;
      onFocus?.();

      expect(modTom.load).toHaveBeenCalledWith("");
    });

    it("does not reload on focus when a query already ran", async () => {
      await setup();
      const onFocus = moduleHandler("focus");
      modTom.lastQuery = "abc";
      onFocus?.();

      expect(modTom.load).not.toHaveBeenCalled();
    });

    it("reflects an object application back onto the application field", async () => {
      await setup("", {
        "/modules/9": {
          application: { "@id": "/applications/7", name: "App 7" },
        },
      });
      moduleHandler("change")?.("/modules/9");

      expect(appTom.addOption).toHaveBeenCalledWith({
        "@id": "/applications/7",
        name: "App 7",
      });
      expect(appTom.setValue).toHaveBeenCalledWith("/applications/7", true);
      expect(extraQuery().application).toBe("/applications/7");
    });

    it("reflects a string application (name falls back to the IRI)", async () => {
      await setup("", { "/modules/3": { application: "/applications/8" } });
      moduleHandler("change")?.("/modules/3");

      expect(appTom.addOption).toHaveBeenCalledWith({
        "@id": "/applications/8",
        name: "/applications/8",
      });
    });

    it("writes onto the native select when there is no application tomselect", async () => {
      await setup("", {
        "/modules/3": { application: { "@id": "/applications/8" } },
      });
      appEl.innerHTML =
        '<option value="/applications/1" selected></option>' +
        '<option value="/applications/8"></option>';
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (appEl as any).tomselect = undefined;
      moduleHandler("change")?.("/modules/3");

      expect(appEl.value).toBe("/applications/8");
    });

    it("ignores an empty value", async () => {
      await setup();
      moduleHandler("change")?.("");

      expect(appTom.addOption).not.toHaveBeenCalled();
    });

    it("ignores a module with no cached data", async () => {
      await setup();
      moduleHandler("change")?.("/modules/unknown");

      expect(appTom.addOption).not.toHaveBeenCalled();
    });

    it("ignores a module whose data has no application", async () => {
      await setup("", { "/modules/4": {} });
      moduleHandler("change")?.("/modules/4");

      expect(appTom.addOption).not.toHaveBeenCalled();
    });

    it("ignores an application object without an @id", async () => {
      await setup("", { "/modules/5": { application: { name: "x" } } });
      moduleHandler("change")?.("/modules/5");

      expect(appTom.addOption).not.toHaveBeenCalled();
    });

    it("does not re-apply when the application is already selected", async () => {
      await setup("", {
        "/modules/9": { application: { "@id": "/applications/7" } },
      });
      appTom.getValue.mockReturnValue("/applications/7");
      moduleHandler("change")?.("/modules/9");

      expect(appTom.addOption).not.toHaveBeenCalled();
    });

    describe("module change resets the list (deferred)", () => {
      beforeEach(() => {
        jest.useFakeTimers();
      });

      it("resets the module list after a module is selected", async () => {
        await setup("", {
          "/modules/9": {
            application: { "@id": "/applications/7", name: "App 7" },
          },
        });
        moduleHandler("change")?.("/modules/9");
        jest.advanceTimersByTime(0);

        expect(modTom.clearOptions).toHaveBeenCalled();
        expect(modTom.clearPagination).toHaveBeenCalled();
        expect(modTom.lastQuery).toBeNull();
      });

      it("resets the module list after the module is cleared", async () => {
        await setup();
        moduleHandler("change")?.("");
        jest.advanceTimersByTime(0);

        expect(modTom.clearOptions).toHaveBeenCalled();
        expect(modTom.clearPagination).toHaveBeenCalled();
      });

      it("reloads an open module list after the deferred reset", async () => {
        await setup("", {
          "/modules/9": { application: { "@id": "/applications/7" } },
        });
        modTom.isOpen = true;
        moduleHandler("change")?.("/modules/9");
        jest.advanceTimersByTime(0);

        expect(modTom.load).toHaveBeenCalledWith("");
      });

      it("does not double-reset while an application filter is being applied", async () => {
        await setup();
        modTom.clear.mockImplementation(() => {
          const onChange = moduleHandler("change");
          onChange?.("");
        });
        appTom.getValue.mockReturnValue("/applications/5");
        applicationHandler()?.("/applications/5");
        modTom.clearOptions.mockClear();
        modTom.clearPagination.mockClear();
        jest.advanceTimersByTime(0);

        expect(modTom.clearOptions).not.toHaveBeenCalled();
        expect(modTom.clearPagination).not.toHaveBeenCalled();
      });
    });
  });

  describe("deferred tomselect", () => {
    beforeEach(() => {
      jest.useFakeTimers();
    });

    it("attaches once the module tomselect appears", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      const modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      appEl.tomselect = { on: jest.fn() } as any;
      application = register();
      await Promise.resolve();

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const modTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        options: {},
      };
      modEl.tomselect = modTom;
      jest.advanceTimersByTime(100);

      expect(modTom.on).toHaveBeenCalled();
    });

    it("reflects a preset module once its data resolves", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      const modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const appTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        addOption: jest.fn(),
        setValue: jest.fn(),
      };
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const modTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => "/modules/9"),
        options: {}, // data not ready at connect -> syncInitial retries
        clear: jest.fn(),
        clearOptions: jest.fn(),
        clearPagination: jest.fn(),
        load: jest.fn(),
        isOpen: false,
        lastQuery: null,
        loadedSearches: {},
      };
      appEl.tomselect = appTom;
      modEl.tomselect = modTom;
      application = register();
      await Promise.resolve();

      modTom.options["/modules/9"] = {
        application: { "@id": "/applications/7", name: "App 7" },
      };
      jest.advanceTimersByTime(100);

      expect(appTom.setValue).toHaveBeenCalledWith("/applications/7", true);
    });
  });

  describe("retry and guard branches", () => {
    beforeEach(() => {
      jest.useFakeTimers();
    });

    it("stops retrying when the module tomselect never appears", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      appEl.tomselect = { on: jest.fn() } as any;
      application = register();
      await Promise.resolve();

      // Module tomselect is never assigned; whenTom should give up after the cap.
      expect(() => jest.advanceTimersByTime(100 * 102)).not.toThrow();
    });

    it("stops retrying when a preset module's data never resolves", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      const modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const appTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        addOption: jest.fn(),
        setValue: jest.fn(),
      };
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const modTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => "/modules/9"),
        options: {}, // data never resolves -> syncInitial exhausts its retries
        clear: jest.fn(),
        clearOptions: jest.fn(),
        clearPagination: jest.fn(),
        load: jest.fn(),
        isOpen: false,
        lastQuery: null,
        loadedSearches: {},
      };
      appEl.tomselect = appTom;
      modEl.tomselect = modTom;
      application = register();
      await Promise.resolve();

      jest.advanceTimersByTime(100 * 82);

      expect(appTom.setValue).not.toHaveBeenCalled();
    });

    it("skips the deferred reset when the module tomselect is gone", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      const modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const appTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        addOption: jest.fn(),
        setValue: jest.fn(),
      };
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const modTom: any = {
        on: jest.fn(),
        getValue: jest.fn(() => ""),
        options: {},
        clear: jest.fn(),
        clearOptions: jest.fn(),
        clearPagination: jest.fn(),
        load: jest.fn(),
        isOpen: false,
        lastQuery: null,
        loadedSearches: {},
      };
      appEl.tomselect = appTom;
      modEl.tomselect = modTom;
      application = register();
      await Promise.resolve();

      const onChange = modTom.on.mock.calls.find(
        (entry: unknown[]) => entry[0] === "change"
      )?.[1] as ((value?: string) => void) | undefined;

      onChange?.("");
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (modEl as any).tomselect = undefined;

      expect(() => jest.advanceTimersByTime(0)).not.toThrow();
      expect(modTom.clearOptions).not.toHaveBeenCalled();
    });

    it("removes its tomselect listeners on disconnect", async () => {
      document.body.innerHTML = HTML;
      const appEl = document.querySelector(
        "#trouble_ticket_application"
      ) as TomSelectElement;
      const modEl = document.querySelector(
        "#trouble_ticket_module"
      ) as TomSelectElement;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const appTom: any = { on: jest.fn(), off: jest.fn() };
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const modTom: any = {
        on: jest.fn(),
        off: jest.fn(),
        getValue: jest.fn(() => ""),
        options: {},
      };
      appEl.tomselect = appTom;
      modEl.tomselect = modTom;
      application = register();
      await Promise.resolve();

      const form = document.querySelector(
        '[data-controller="mis--trouble-ticket--application-module"]'
      ) as HTMLElement;
      form.remove();
      await Promise.resolve();

      expect(appTom.off).toHaveBeenCalledWith("change", expect.any(Function));
      expect(modTom.off).toHaveBeenCalledWith("focus", expect.any(Function));
      expect(modTom.off).toHaveBeenCalledWith("change", expect.any(Function));
    });
  });
});
