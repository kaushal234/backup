import { describe, expect, jest } from "@jest/globals";
import { Application, Controller } from "@hotwired/stimulus";
import AutofillProjectManagerController from "../../../controllers/mis/project/autofill_project_manager_controller";
import { fetch } from "../../../controllers/utils/client";
import { startStimulus } from "../../helper/setup";
import type { TomSelectElement } from "../../../types/tomselect";

jest.mock("../../../controllers/utils/client", () => ({
  fetch: jest.fn(),
}));

describe("test errors", () => {
  it("should throw error when no api url is provided", async () => {
    let thrownError: Error | null = null;
    delete process.env.WEBPACK_API_URI;
    document.body.innerHTML = `
      <select
        data-controller="mis--project--autofill-project-manager autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
    `;
    const application = Application.start();
    application.register(
      "mis--project--autofill-project-manager",
      AutofillProjectManagerController
    );
    application.register(
      "symfony--ux-autocomplete--autocomplete",
      class extends Controller {}
    );
    application.handleError = (error: Error) => {
      thrownError = error;
    };
    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "WEBPACK_API_URI is not defined",
    });
  });

  it("should throw error when no project module", async () => {
    let thrownError: Error | null = null;
    process.env.WEBPACK_API_URI = "http://api/";
    document.body.innerHTML = `
      <select
        data-controller="mis--project--autofill-project-manager autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
    `;
    const application = Application.start();
    application.register(
      "mis--project--autofill-project-manager",
      AutofillProjectManagerController
    );
    application.register(
      "symfony--ux-autocomplete--autocomplete",
      class extends Controller {}
    );
    application.handleError = (error: Error) => {
      thrownError = error;
    };
    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "Module or projectManager not found",
    });
  });

  it("should throw error when no project manager", async () => {
    let thrownError: Error | null = null;
    process.env.WEBPACK_API_URI = "http://api/";
    document.body.innerHTML = `
      <select
        id="project_module"
        data-controller="mis--project--autofill-project-manager autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ></select>
    `;
    const application = Application.start();
    application.register(
      "mis--project--autofill-project-manager",
      AutofillProjectManagerController
    );
    application.register(
      "symfony--ux-autocomplete--autocomplete",
      class extends Controller {}
    );
    application.handleError = (error: Error) => {
      thrownError = error;
    };
    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "Module or projectManager not found",
    });
  });
});

describe("Test AutofillProjectManagerController", () => {
  let source: HTMLSelectElement;
  let target: TomSelectElement;

  beforeEach(async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    document.body.innerHTML = `
      <select
        id="project_module"
        data-controller="mis--project--autofill-project-manager autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="foo"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id", "labelField": ""}'
      >
        <option></option>
      </select>
      <select 
        id="project_projectManager"
        data-controller="autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="bar"
        data-symfony--ux-autocomplete--autocomplete-tom-select-options-value='{"valueField":"@id", "labelField": ""}'
      >
        <option></option>
      </select>
    `;

    source = document.querySelector("#project_module") as HTMLSelectElement;
    target = document.querySelector(
      "#project_projectManager"
    ) as TomSelectElement;
  });

  it("should select project manager", async () => {
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
    };

    source.addEventListener("autocomplete:pre-connect", spy);
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

    options.onItemAdd("42");

    await Promise.resolve();
    await Promise.resolve();

    expect(spy).toHaveBeenCalled();
    expect(target.tomselect.addOption).toHaveBeenCalledWith({
      "@id": "/module/1/operationalOwner",
      text: "Doe John",
    });
    expect(target.tomselect.addItem).toHaveBeenCalledWith(
      "/module/1/operationalOwner"
    );
    expect(mockFetch).toHaveBeenCalledWith(new URL("42", "http://api/"));
  });

  it("should show console error on API failed", async () => {
    const application = startStimulus();
    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;

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

    expect(consoleSpy).toHaveBeenCalledWith("Failed to fetch people", {
      error: expect.any(Error),
      id: "42",
    });
  });
});
