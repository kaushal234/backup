import { describe, expect, jest } from "@jest/globals";
import { clearDOM, mountDOM } from "./helper/dom";
import { startStimulus } from "./helper/setup";
import { fetch } from "../controllers/utils/client";
import type { TomSelectElement } from "../types/tomselect";

jest.mock("../controllers/utils/client", () => ({
  fetch: jest.fn(),
}));

describe("test errors", () => {
  afterEach(() => {
    clearDOM();
  });

  it("should throw error when no api url is provided", async () => {
    delete process.env.WEBPACK_API_URI;
    mountDOM(`
      <select
        data-controller="cascading-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-cascading-select-target-form-value="target"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
      <select id="target"></select>
    `);

    const target = document.querySelector("#target") as HTMLSelectElement;
    const application = startStimulus();

    let thrownError: Error | null = null;
    application.handleError = (error: Error) => {
      thrownError = error;
    };

    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "WEBPACK_API_URI is not defined",
    });
    expect(target.disabled).toBe(true);
  });

  it("should throw error when no target", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <select
        data-controller="cascading-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-cascading-select-target-form-value="target"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
    `);

    const application = startStimulus();

    let thrownError: Error | null = null;
    application.handleError = (error: Error) => {
      thrownError = error;
    };

    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "Element #target not found.",
    });
  });
});

describe("test cascading select", () => {
  it("should add option on target", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <select
        id="source"
        data-controller="cascading-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-cascading-select-target-form-value="target"
        data-cascading-select-from-property-value="@id"
        data-cascading-select-to-filter-value="target.filter"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
      <select id="target"><option selected value="42">John Doe</option></select>
    `);
    const source = document.querySelector("#source") as TomSelectElement;
    const target = document.querySelector("#target") as TomSelectElement;
    const application = startStimulus();

    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      json: () => ({
        id: "/module/1",
        operationalOwner: {
          "@id": "/module/1/operationalOwner",
          firstname: "John",
          lastname: "Doe",
        },
      }),
    } as unknown as Response);
    const spy = jest.fn();

    target.tomselect = {
      settings: {
        valueField: "@id",
        labelField: "text",
      },
      addOption: jest.fn(),
      addItem: jest.fn(),
      clear: jest.fn(),
      clearOptions: jest.fn(),
      clearCache: jest.fn(),
      on: jest.fn(),
      lastQuery: "",
      load: jest.fn(),
      enable: jest.fn(),
      disable: jest.fn(),
    };

    source.addEventListener("autocomplete:pre-connect", spy);

    await application.start();

    const options = {
      onItemAdd: jest.fn(),
      onClear: jest.fn(),
      render: {
        no_results: jest.fn(),
      },
    };

    source.dispatchEvent(
      new CustomEvent("autocomplete:pre-connect", {
        bubbles: true,
        detail: { options },
      })
    );

    options.onItemAdd("42");

    await Promise.resolve();
    await Promise.resolve();

    expect(target.disabled).toBe(false);
    expect(spy).toHaveBeenCalled();
    expect(target.tomselect.clear).toHaveBeenCalled();
    expect(target.tomselect.clearOptions).toHaveBeenCalled();
    expect(target.tomselect.clearCache).toHaveBeenCalled();
    expect(target.tomselect.on).toHaveBeenCalled();
    expect(target.tomselect.enable).toHaveBeenCalled();

    options.onClear();

    expect(target.tomselect.disable).toHaveBeenCalled();
  });

  it("should throw error when API failed", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <select
        id="source"
        data-controller="cascading-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-cascading-select-target-form-value="target"
        data-cascading-select-from-property-value="@id"
        data-cascading-select-to-filter-value="target.filter"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
      <select id="target"><option selected value="42">John Doe</option></select>
    `);

    const application = startStimulus();
    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    const source = document.querySelector("#source") as TomSelectElement;
    const target = document.querySelector("#target") as TomSelectElement;

    mockFetch.mockRejectedValue(new Error("API Error"));

    const consoleSpy = jest
      .spyOn(console, "error")
      .mockImplementation(() => jest.fn());

    target.tomselect = {
      settings: {
        valueField: "@id",
        labelField: "text",
      },
      addOption: jest.fn(),
      addItem: jest.fn(),
    };

    source.addEventListener("autocomplete:pre-connect", jest.fn());
    await application.start();

    const options = {
      onItemAdd: jest.fn(),
      render: {
        no_results: jest.fn(),
      },
    };

    source.dispatchEvent(
      new CustomEvent("autocomplete:pre-connect", {
        bubbles: true,
        detail: { options },
      })
    );

    await options.onItemAdd("42");

    expect(consoleSpy).toHaveBeenCalledWith("Failed to fetch target element.", {
      error: expect.any(Error),
      id: "42",
    });
  });
});
