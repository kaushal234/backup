import { describe, expect, jest, afterEach } from "@jest/globals";
import { Application, Controller } from "@hotwired/stimulus";
import { clearDOM, mountDOM } from "../../helper/dom";
import { fetch } from "../../../controllers/utils/client";
import ErpSourceSelectController, {
  ERP_CHANGED_EVENT,
  getCurrentErp,
} from "../../../controllers/quality/first_article_qualification/erp_source_select_controller";
import type { TomSelectElement } from "../../../types/tomselect";

jest.mock("../../../controllers/utils/client", () => ({
  fetch: jest.fn(),
}));

let stimulusApp: Application | undefined;

function startStimulus(): Application {
  const app = Application.start();
  app.register(
    "quality--first-article-qualification--erp-source-select",
    ErpSourceSelectController
  );
  app.register("autocomplete", class extends Controller {});
  app.register(
    "symfony--ux-autocomplete--autocomplete",
    class extends Controller {}
  );
  stimulusApp = app;
  return app;
}

describe("erp source select controller", () => {
  afterEach(() => {
    stimulusApp?.stop();
    stimulusApp = undefined;
    clearDOM();
    delete document.documentElement.dataset.currentErp;
  });

  it("should throw error when no api url is provided", async () => {
    delete process.env.WEBPACK_API_URI;
    mountDOM(`
      <select
        data-controller="quality--first-article-qualification--erp-source-select"
      ></select>
    `);

    const app = startStimulus();

    let thrownError: Error | null = null;
    app.handleError = (error: Error) => {
      thrownError = error;
    };

    await Promise.resolve();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError).toMatchObject({
      message: "baseUrl is not defined",
    });
  });

  it("should set currentErp and dispatch event on item add", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <select
        id="source"
        data-controller="quality--first-article-qualification--erp-source-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="locations"
      ></select>
    `);

    const source = document.querySelector("#source") as TomSelectElement;
    const app = startStimulus();

    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      json: () => ({ erp: "500" }),
    } as unknown as Response);

    const listener = jest.fn();
    window.addEventListener(ERP_CHANGED_EVENT, listener);

    await app.start();

    const options = {
      onItemAdd: jest.fn(),
      onClear: jest.fn(),
    };

    source.dispatchEvent(
      new CustomEvent("autocomplete:pre-connect", {
        bubbles: true,
        detail: { options },
      })
    );

    await options.onItemAdd("/locations/20");
    await Promise.resolve();

    expect(listener).toHaveBeenCalled();
    expect(getCurrentErp()).toBe("500");

    options.onClear();

    expect(getCurrentErp()).toBeNull();

    window.removeEventListener(ERP_CHANGED_EVENT, listener);
  });

  it("should fetch and set erp when the select already has a value on connect", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
    <select
      id="source"
      data-controller="quality--first-article-qualification--erp-source-select autocomplete symfony--ux-autocomplete--autocomplete"
      data-symfony--ux-autocomplete--autocomplete-url-value="locations"
    >
      <option selected value="/locations/20">TLD MTL</option>
    </select>
  `);

    const source = document.querySelector("#source") as HTMLSelectElement;
    source.value = "/locations/20";

    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      json: () => ({ erp: "500" }),
    } as unknown as Response);

    const app = startStimulus();
    await app.start();
    await Promise.resolve();
    await Promise.resolve();

    expect(getCurrentErp()).toBe("500");
  });

  it("should log an error when the API call fails", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <select
        id="source"
        data-controller="quality--first-article-qualification--erp-source-select autocomplete symfony--ux-autocomplete--autocomplete"
        data-symfony--ux-autocomplete--autocomplete-url-value="locations"
      ></select>
    `);

    const source = document.querySelector("#source") as TomSelectElement;
    const app = startStimulus();
    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    mockFetch.mockRejectedValue(new Error("API Error"));

    const consoleSpy = jest
      .spyOn(console, "error")
      .mockImplementation(() => jest.fn());

    await app.start();

    const options = {
      onItemAdd: jest.fn(),
      onClear: jest.fn(),
    };

    source.dispatchEvent(
      new CustomEvent("autocomplete:pre-connect", {
        bubbles: true,
        detail: { options },
      })
    );

    await options.onItemAdd("/locations/20");

    expect(consoleSpy).toHaveBeenCalledWith(
      "Failed to fetch factory element.",
      {
        error: expect.any(Error),
        id: "/locations/20",
      }
    );
  });
});
