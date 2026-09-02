import { describe, expect, jest } from "@jest/globals";
import TomSelect from "tom-select";
import { Application, Controller } from "@hotwired/stimulus";
import AutocompleteController, {
  buildQuery,
  optionsLoad,
} from "../controllers/autocomplete_controller";
import { fetch } from "../controllers/utils/client";

jest.mock("../controllers/utils/client", () => ({
  fetch: jest.fn(),
}));

describe("AutocompleteController", () => {
  describe("test buildQuery", () => {
    let params: URLSearchParams;

    beforeEach(() => {
      params = new URLSearchParams();
    });

    it("should append simple key/value pairs", () => {
      buildQuery(params, { foo: "bar", num: 42 });

      expect(params.get("foo")).toBe("bar");
      expect(params.get("num")).toBe("42");
    });

    it("should handle arrays", () => {
      buildQuery(params, { tags: ["a", "b"] });

      expect(params.getAll("tags[]")).toEqual(["a", "b"]);
    });

    it("should handle nested objects", () => {
      buildQuery(params, {
        filter: {
          name: "john",
          age: 30,
        },
      });

      expect(params.get("filter[name]")).toBe("john");
      expect(params.get("filter[age]")).toBe("30");
    });

    it("should handle deeply nested objects", () => {
      buildQuery(params, {
        filter: {
          user: {
            name: "john",
          },
        },
      });

      expect(params.get("filter[user][name]")).toBe("john");
    });

    it("should ignore null and undefined values", () => {
      buildQuery(params, {
        foo: null,
        bar: undefined,
        valid: "ok",
      });

      expect(params.get("foo")).toBeNull();
      expect(params.get("bar")).toBeNull();
      expect(params.get("valid")).toBe("ok");
    });

    it("should work with prefix", () => {
      buildQuery(params, { foo: "bar" }, "parent");

      expect(params.get("parent[foo]")).toBe("bar");
    });

    it("should handle array inside nested object", () => {
      buildQuery(params, {
        filter: {
          tags: ["a", "b"],
        },
      });

      expect(params.getAll("filter[tags][]")).toEqual(["a", "b"]);
    });
  });

  describe("test errors", () => {
    beforeEach(() => {
      delete process.env.WEBPACK_API_URI;
    });

    it("should throw error when no api url is provided", async () => {
      document.body.innerHTML = `
      <select
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>

    `;

      let thrownError: Error | null = null;
      const application = Application.start();
      application.handleError = (error: Error) => {
        thrownError = error;
      };

      application.register("autocomplete", AutocompleteController);
      application.register(
        "symfony--ux-autocomplete--autocomplete",
        class extends Controller {}
      );
      await Promise.resolve();

      expect(thrownError).toBeInstanceOf(Error);
      expect(thrownError).toMatchObject({
        message: "WEBPACK_API_URI is not defined",
      });
    });
  });

  describe("test optionsLoad", () => {
    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    let callback: jest.Mock;
    let tomSelect: Partial<TomSelect>;

    beforeEach(() => {
      jest.clearAllMocks();
      callback = jest.fn();
      tomSelect = {
        getUrl: jest
          .fn()
          .mockReturnValue(new URL("https://api.test/users?q=john")),
        settings: {
          valueField: "id",
        },
      } as unknown as TomSelect;
    });

    it("should call callback with mapped results", async () => {
      mockFetch.mockResolvedValue({
        json: () => ({
          "hydra:member": [
            { id: "1", name: "John" },
            { id: "2", name: "Jane" },
          ],
          "hydra:view": {},
        }),
      } as unknown as Response);

      await optionsLoad(
        tomSelect as TomSelect,
        "john",
        callback,
        "https://api.test",
        []
      );

      expect(fetch).toHaveBeenCalledWith(
        new URL("https://api.test/users?q=john")
      );
      expect(callback).toHaveBeenCalledWith([
        { id: "1", name: "John", disabled: false },
        { id: "2", name: "Jane", disabled: false },
      ]);
    });

    it("should call setNextUrl when hydra:next exists", async () => {
      const setNextUrl = jest.fn();

      tomSelect = {
        getUrl: jest
          .fn()
          .mockReturnValue(new URL("https://api.test/users?q=john")),
        setNextUrl,
        settings: {
          valueField: "id",
        },
      } as unknown as TomSelect;

      mockFetch.mockResolvedValue({
        json: () => ({
          "hydra:member": [],
          "hydra:view": {
            "hydra:next": "/users?page=2",
          },
        }),
      } as unknown as Response);

      await optionsLoad(
        tomSelect as TomSelect,
        "john",
        callback,
        "https://api.test",
        []
      );

      expect(setNextUrl).toHaveBeenCalledWith(
        "john",
        new URL("/users?page=2", "https://api.test")
      );
    });

    it("should call callback with no args when fetch fails", async () => {
      mockFetch.mockRejectedValue(new Error("API error"));

      await optionsLoad(
        tomSelect as TomSelect,
        "john",
        callback,
        "https://api.test",
        []
      );

      expect(callback).toHaveBeenCalledWith();
    });
  });

  describe("test AutocompleteController", () => {
    let application: Application;

    let element: HTMLSelectElement;

    beforeEach(async () => {
      process.env.WEBPACK_API_URI = "http://api/";
      document.body.innerHTML = `
      <select
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
        data-autocomplete-items-per-page-value="10"
        data-autocomplete-page-value="1"
        data-autocomplete-template-selection-value="{{foo}} {{bar}}"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id"}'
        data-autocomplete-allow-clear-value="false"
      >   
      </select>
    `;

      element = document.querySelector("select") as HTMLSelectElement;
      application = Application.start();
      application.register("autocomplete", AutocompleteController);
      application.register(
        "symfony--ux-autocomplete--autocomplete",
        class extends Controller {}
      );
      await Promise.resolve();
    });

    afterEach(() => {
      application.stop();
      document.body.innerHTML = "";
    });

    it("should init autocomplete", async () => {
      const tomSelectMock: Partial<TomSelect> = {
        getUrl: jest.fn(() => new URL("https://api.test/users?q=42")),
        setNextUrl: jest.fn(),
        settings: {
          valueField: "id",
        },
      } as unknown as TomSelect;

      const spy = jest.fn();

      element.addEventListener("autocomplete:pre-connect", spy);
      await application.start();

      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const options = {
        clearAfterSelect: false,
        render: {
          option: () => {},
          item: () => {},
        },
        plugins: {
          clear_button: true,
        },
        firstUrl: jest.fn(),
        load: jest.fn(),
      };

      element.dispatchEvent(
        new CustomEvent("autocomplete:pre-connect", {
          bubbles: true,
          detail: { options },
        })
      );

      expect(spy).toHaveBeenCalled();
      expect(options.clearAfterSelect).toBe(true);
      expect(options.plugins.clear_button).toBeUndefined();
      expect(options.render.option).toBe(controller.optionsRenderOption);
      expect(options.render.item).toBe(controller.optionsRenderItem);
      expect(options.firstUrl("42")).toBe(
        "http://api/foo?q=42&itemsPerPage=10&page=1"
      );

      const callback = jest.fn();
      await options.load.call(tomSelectMock, "42", callback);
      expect(callback).toHaveBeenCalled();
    });

    it("should init with light options", async () => {
      process.env.WEBPACK_API_URI = "http://api/";
      document.body.innerHTML = `
      <select
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id"}'
        data-autocomplete-extra-query-value='{"order":{"name":"ASC"}}'
      >   
      </select>
    `;

      element = document.querySelector("select") as HTMLSelectElement;
      application = Application.start();
      application.register("autocomplete", AutocompleteController);
      application.register(
        "symfony--ux-autocomplete--autocomplete",
        class extends Controller {}
      );
      await Promise.resolve();

      const spy = jest.fn();

      element.addEventListener("autocomplete:pre-connect", spy);
      application.start();

      const options = {
        firstUrl: jest.fn(),
        render: {
          no_results: jest.fn(),
        },
      };

      element.dispatchEvent(
        new CustomEvent("autocomplete:pre-connect", {
          bubbles: true,
          detail: { options },
        })
      );

      expect(options.firstUrl("")).toBe("http://api/foo?order%5Bname%5D=ASC");
      expect(options.firstUrl("42")).toBe(
        "http://api/foo?q=42&order%5Bname%5D=ASC"
      );

      const tomSelect: Partial<TomSelect> = {
        options: {
          "/users/1": {
            nameUnresolved: false,
          },
        },
        updateOption: jest.fn(),
      } as unknown as TomSelect;
      const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
      mockFetch.mockResolvedValue({
        json: jest.fn(),
      } as unknown as Response);
      element.dispatchEvent(
        new CustomEvent("autocomplete:connect", {
          bubbles: true,
          detail: { tomSelect },
        })
      );
      await Promise.resolve();
      await Promise.resolve();

      expect(mockFetch).not.toHaveBeenCalled();
      expect(options.render.no_results()).toBe(
        '<div class="no-results">No results found</div>'
      );
    });

    it("test onConnect", async () => {
      const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
      mockFetch.mockResolvedValue({
        json: () => ({
          id: "/users/1",
          name: "John",
        }),
      } as unknown as Response);
      const updateOptionMock = jest.fn();
      const tomSelect: Partial<TomSelect> = {
        getUrl: jest.fn(() => new URL("https://api.test/users?q=42")),
        setNextUrl: jest.fn(),
        settings: {
          valueField: "id",
        },
        options: {
          "/users/1": {
            nameUnresolved: true,
          },
        },
        updateOption: updateOptionMock,
      } as unknown as TomSelect;
      const spy = jest.fn();

      element.addEventListener("autocomplete:connect", spy);
      await application.start();

      const options = {
        clearAfterSelect: false,
        render: {
          option: () => {},
          item: () => {},
        },
        plugins: {
          clear_button: true,
        },
        firstUrl: jest.fn(),
        load: jest.fn(),
      };

      element.dispatchEvent(
        new CustomEvent("autocomplete:connect", {
          bubbles: true,
          detail: { options, tomSelect },
        })
      );

      await Promise.resolve();
      await Promise.resolve();

      expect(mockFetch).toHaveBeenCalledWith(
        new URL("/users/1", "http://api/")
      );
      expect(updateOptionMock).toHaveBeenCalledWith("/users/1", {
        nameUnresolved: true,
        id: "/users/1",
        name: "John",
      });
    });
  });

  describe("test optionsRender", () => {
    let application: Application;

    let element: HTMLSelectElement;

    beforeEach(async () => {
      process.env.WEBPACK_API_URI = "http://api/";
      document.body.innerHTML = `
      <select
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
        data-autocomplete-items-per-page-value="10"
        data-autocomplete-page-value="1"
        data-autocomplete-template-selection-value="{{foo}} {{bar}}"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id"}'
        data-autocomplete-allow-clear-value="false"
      >
      </select>
    `;

      element = document.querySelector("select") as HTMLSelectElement;
      application = Application.start();
      application.register("autocomplete", AutocompleteController);
      application.register(
        "symfony--ux-autocomplete--autocomplete",
        class extends Controller {}
      );
      await Promise.resolve();
    });

    afterEach(() => {
      application.stop();
      document.body.innerHTML = "";
    });

    it("should render option with text property", () => {
      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const result = controller.optionsRenderOption({
        text: "foo",
      });

      expect(result).toBe("<div>foo</div>");
    });

    it("should render option with template selection", () => {
      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const result = controller.optionsRenderOption({
        foo: "42",
        bar: "six seven",
      });

      expect(result).toBe("<div>42 six seven</div>");
    });

    it("should render option with template result", async () => {
      document.body.innerHTML = `
      <select
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
        data-autocomplete-items-per-page-value="10"
        data-autocomplete-page-value="1"
        data-autocomplete-template-selection-value="{{foo}} {{bar}}"
        data-autocomplete-template-result-value="{{bar}} {{foo}}"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id"}'
        data-autocomplete-allow-clear-value="false"
      >
      </select>
    `;

      element = document.querySelector("select") as HTMLSelectElement;
      application = Application.start();
      application.register("autocomplete", AutocompleteController);
      application.register(
        "symfony--ux-autocomplete--autocomplete",
        class extends Controller {}
      );
      await Promise.resolve();

      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const result = controller.optionsRenderOption({
        foo: "42",
        bar: "six seven",
      });

      expect(result).toBe("<div>six seven 42</div>");
    });

    it("shuold render item with text", async () => {
      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const result = controller.optionsRenderItem({
        text: "42",
      });

      expect(result).toBe("<div>42</div>");
    });

    it("shuold render item with template", async () => {
      const controller = application.getControllerForElementAndIdentifier(
        element,
        "autocomplete"
      ) as AutocompleteController;

      const result = controller.optionsRenderItem({
        foo: "42",
        bar: "six seven",
      });

      expect(result).toBe("<div>42 six seven</div>");
    });
  });
});
