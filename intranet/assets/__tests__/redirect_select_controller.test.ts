import {
  describe,
  expect,
  it,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application, Controller } from "@hotwired/stimulus";
import Routing from "fos-router";
import RedirectSelectController from "../controllers/redirect_select_controller";
import AutocompleteController from "../controllers/autocomplete_controller";
import { navigate } from "../controllers/utils/navigate";

jest.mock("fos-router", () => ({
  generate: jest.fn(),
}));

jest.mock("../controllers/utils/navigate", () => ({
  navigate: jest.fn(),
}));

let application: Application | undefined;

/**
 * Mount the controller on a select, then replay the autocomplete lifecycle
 * (pre-connect to grab the onChange hook, connect to inject the TomSelect data).
 * Returns the onChange callback the controller registered.
 */
async function setup(
  attrs: Record<string, string>,
  tomSelectOptions: Record<string, unknown> = {}
): Promise<(value: string) => void> {
  process.env.WEBPACK_API_URI = "http://api/";
  document.documentElement.lang = "en";

  const dataAttrs = Object.entries(attrs)
    .map(([key, value]) => `${key}='${value}'`)
    .join(" ");

  document.body.innerHTML = `
    <select
      data-controller="redirect-select autocomplete symfony--ux-autocomplete--autocomplete"
      data-symfony--ux-autocomplete--autocomplete-url-value="foo"
      ${dataAttrs}
    ></select>
  `;

  const element = document.querySelector("select") as HTMLSelectElement;

  application = Application.start();
  application.register("autocomplete", AutocompleteController);
  application.register("redirect-select", RedirectSelectController);
  application.register(
    "symfony--ux-autocomplete--autocomplete",
    class extends Controller {}
  );
  await application.start();

  // Shape expected by the real autocomplete controller's pre-connect handler.
  const options: {
    onChange?: (value: string) => void;
    render: Record<string, unknown>;
  } = { render: {} };

  element.dispatchEvent(
    new CustomEvent("autocomplete:pre-connect", {
      bubbles: true,
      detail: { options },
    })
  );
  element.dispatchEvent(
    new CustomEvent("autocomplete:connect", {
      bubbles: true,
      detail: { tomSelect: { options: tomSelectOptions } },
    })
  );

  return options.onChange as (value: string) => void;
}

describe("redirect select controller", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    (Routing.generate as jest.Mock).mockReturnValue("/generated");
  });

  afterEach(() => {
    application?.stop();
    application = undefined;
    document.body.innerHTML = "";
  });

  it("maps a selected object property to a route param and redirects", async () => {
    const onChange = await setup(
      {
        "data-redirect-select-route-value": "directory_people_show",
        "data-redirect-select-route-params-map-value": '{"@id":"id"}',
      },
      { "/people/42": { "@id": "/people/42", firstname: "Jane" } }
    );

    (Routing.generate as jest.Mock).mockReturnValue("/directory/42");
    onChange("/people/42");

    expect(Routing.generate).toHaveBeenCalledWith("directory_people_show", {
      id: "/people/42",
    });
    expect(navigate).toHaveBeenCalledWith("/en/private/directory/42");
  });

  it("does nothing when the selection is cleared (empty value)", async () => {
    const onChange = await setup({
      "data-redirect-select-route-value": "directory_people_show",
      "data-redirect-select-route-params-map-value": '{"@id":"id"}',
    });

    onChange("");

    expect(Routing.generate).not.toHaveBeenCalled();
    expect(navigate).not.toHaveBeenCalled();
  });

  it("falls back to the selected value when the mapped property is missing", async () => {
    const onChange = await setup(
      {
        "data-redirect-select-route-value": "show",
        "data-redirect-select-route-params-map-value": '{"id":"id"}',
      },
      { "42": { "@id": "/people/42" } } // no "id" property on the item
    );

    onChange("42");

    expect(Routing.generate).toHaveBeenCalledWith("show", { id: "42" });
  });

  it("resolves a dotted property path", async () => {
    const onChange = await setup(
      {
        "data-redirect-select-route-value": "show",
        "data-redirect-select-route-params-map-value":
          '{"businessUnit.id":"bu"}',
      },
      { "/people/42": { businessUnit: { id: 7 } } }
    );

    onChange("/people/42");

    expect(Routing.generate).toHaveBeenCalledWith("show", { bu: 7 });
  });

  it("merges extra params with the mapped params", async () => {
    const onChange = await setup(
      {
        "data-redirect-select-route-value": "trouble_ticket_assign_to_team",
        "data-redirect-select-route-params-map-value": '{"id":"assigneeId"}',
        "data-redirect-select-route-params-extra-value": '{"id":99}',
      },
      { "/people/42": { id: 42 } }
    );

    onChange("/people/42");

    expect(Routing.generate).toHaveBeenCalledWith(
      "trouble_ticket_assign_to_team",
      { id: 99, assigneeId: 42 }
    );
  });

  it("works as a simple select with no map (extra params only)", async () => {
    const onChange = await setup({
      "data-redirect-select-route-value": "list",
      "data-redirect-select-route-params-extra-value": '{"tab":"info"}',
    });

    onChange("open");

    expect(Routing.generate).toHaveBeenCalledWith("list", { tab: "info" });
  });
});
