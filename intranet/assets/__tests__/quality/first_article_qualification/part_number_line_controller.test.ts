import { describe, expect, jest } from "@jest/globals";
import { Application, Controller } from "@hotwired/stimulus";
import { clearDOM, mountDOM } from "../../helper/dom";
import { fetch } from "../../../controllers/utils/client";
import { ERP_CHANGED_EVENT } from "../../../controllers/quality/first_article_qualification/erp_source_select_controller";
import PartNumberLineController from "../../../controllers/quality/first_article_qualification/part_number_line_controller";
import type { TomSelectElement } from "../../../types/tomselect";

jest.mock("../../../controllers/utils/client", () => ({
  fetch: jest.fn(),
}));

let stimulusApp: Application | undefined;

function startStimulus(): Application {
  const app = Application.start();
  app.register(
    "quality--first-article-qualification--part-number-line",
    PartNumberLineController
  );
  app.register("autocomplete", class extends Controller {});
  app.register(
    "symfony--ux-autocomplete--autocomplete",
    class extends Controller {}
  );
  stimulusApp = app;
  return app;
}

describe("part number line controller", () => {
  afterEach(() => {
    stimulusApp?.stop();
    stimulusApp = undefined;
    clearDOM();
    delete document.documentElement.dataset.currentErp;
  });

  it("should be disabled by default when no erp is known", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <div data-controller="quality--first-article-qualification--part-number-line">
        <select
          data-quality--first-article-qualification--part-number-line-target="number"
          data-symfony--ux-autocomplete--autocomplete-url-value="ion/items"
        ></select>
        <input data-quality--first-article-qualification--part-number-line-target="revision">
        <input data-quality--first-article-qualification--part-number-line-target="description">
      </div>
    `);

    const app = startStimulus();
    await app.start();

    const numberTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="number"]'
    ) as HTMLSelectElement;

    expect(numberTarget.disabled).toBe(true);
  });

  it("should enable and filter by site when erp is emitted", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <div data-controller="quality--first-article-qualification--part-number-line">
        <select
          data-quality--first-article-qualification--part-number-line-target="number"
          data-symfony--ux-autocomplete--autocomplete-url-value="ion/items"
        ></select>
        <input data-quality--first-article-qualification--part-number-line-target="revision">
        <input data-quality--first-article-qualification--part-number-line-target="description">
      </div>
    `);

    const app = startStimulus();
    await app.start();

    const numberTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="number"]'
    ) as TomSelectElement;

    numberTarget.tomselect = {
      enable: jest.fn(),
      disable: jest.fn(),
    } as unknown as TomSelectElement["tomselect"];

    window.dispatchEvent(
      new CustomEvent(ERP_CHANGED_EVENT, { detail: { erp: "500" } })
    );

    expect(numberTarget.disabled).toBe(false);
    expect(numberTarget.tomselect.enable).toHaveBeenCalled();

    const extraQuery = JSON.parse(
      numberTarget.dataset.autocompleteExtraQueryValue || "{}"
    );
    expect(extraQuery.site).toBe("500");
  });

  it("should not disable/enable a line that already has a value", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <div data-controller="quality--first-article-qualification--part-number-line">
        <select
          data-quality--first-article-qualification--part-number-line-target="number"
          data-symfony--ux-autocomplete--autocomplete-url-value="ion/items"
        >
          <option selected value="/ion/items/site=500;item=0024635">0024635</option>
        </select>
        <input data-quality--first-article-qualification--part-number-line-target="revision">
        <input data-quality--first-article-qualification--part-number-line-target="description">
      </div>
    `);

    const app = startStimulus();
    await app.start();

    const numberTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="number"]'
    ) as TomSelectElement;
    numberTarget.value = "/ion/items/site=500;item=0024635";

    numberTarget.tomselect = {
      enable: jest.fn(),
      disable: jest.fn(),
    } as unknown as TomSelectElement["tomselect"];

    window.dispatchEvent(
      new CustomEvent(ERP_CHANGED_EVENT, { detail: { erp: null } })
    );

    expect(numberTarget.disabled).toBe(false);
    expect(numberTarget.tomselect.disable).not.toHaveBeenCalled();
  });

  it("should auto-fill revision and description on item selection", async () => {
    process.env.WEBPACK_API_URI = "http://api/";
    mountDOM(`
      <div data-controller="quality--first-article-qualification--part-number-line">
        <select
          data-quality--first-article-qualification--part-number-line-target="number"
          data-symfony--ux-autocomplete--autocomplete-url-value="ion/items"
        ></select>
        <input data-quality--first-article-qualification--part-number-line-target="revision">
        <input data-quality--first-article-qualification--part-number-line-target="description">
      </div>
    `);

    const numberTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="number"]'
    ) as TomSelectElement;

    const app = startStimulus();

    const mockFetch = fetch as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      json: () => ({ revision: "A", itemDescription: "WD40" }),
    } as unknown as Response);

    await app.start();

    const options = {
      onItemAdd: jest.fn(),
    };

    numberTarget.dispatchEvent(
      new CustomEvent("autocomplete:pre-connect", {
        bubbles: true,
        detail: { options },
      })
    );

    await options.onItemAdd("/ion/items/site=500;item=0024635");
    await Promise.resolve();

    const revisionTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="revision"]'
    ) as HTMLInputElement;
    const descriptionTarget = document.querySelector(
      '[data-quality--first-article-qualification--part-number-line-target="description"]'
    ) as HTMLInputElement;

    expect(revisionTarget.value).toBe("A");
    expect(descriptionTarget.value).toBe("WD40");
  });
});
