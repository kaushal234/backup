import { Application } from "@hotwired/stimulus";
import { describe, it, expect, beforeEach, afterEach } from "@jest/globals";
import PlanLineTypeController from "../../../controllers/quality/first_article_qualification/plan_line_type_controller";

function requireElement<T extends Element>(selector: string): T {
  const element = document.querySelector<T>(selector);
  if (element === null) {
    throw new Error(`Element not found: ${selector}`);
  }
  return element;
}

const CONTROLLER_ID = "quality--first-article-qualification--plan-line-type";

const TYPES = [
  {
    "@id": "/types/1",
    requestablePriorDelivery: true,
    requestableAtPurchaseOrder: false,
  },
  {
    "@id": "/types/2",
    requestablePriorDelivery: false,
    requestableAtPurchaseOrder: false,
  },
];

function mountDom(): void {
  document.body.innerHTML = `
    <div data-controller="${CONTROLLER_ID}"
         data-${CONTROLLER_ID}-types-value='${JSON.stringify(TYPES)}'>
      <select data-${CONTROLLER_ID}-target="type"
              data-action="${CONTROLLER_ID}#updateCheckboxes">
        <option value="">—</option>
        <option value="/types/1">A</option>
        <option value="/types/2">B</option>
      </select>
      <input type="checkbox" data-${CONTROLLER_ID}-target="prior" />
      <input type="checkbox" data-${CONTROLLER_ID}-target="purchase" />
    </div>`;
}

describe("PlanLineTypeController", () => {
  let application: Application;

  beforeEach(async () => {
    mountDom();
    application = Application.start();
    application.register(CONTROLLER_ID, PlanLineTypeController);
    await new Promise((r) => {
      setTimeout(r, 0);
    });
  });

  afterEach(() => {
    application.stop();
    document.body.innerHTML = "";
  });

  it("Gray out both checkboxes when no type is selected", () => {
    const prior = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="prior"]`
    );
    const purchase = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="purchase"]`
    );
    expect(prior.disabled).toBe(true);
    expect(purchase.disabled).toBe(true);
  });

  it("only enables the checkbox allowed by the type", async () => {
    const select = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="type"]`
    );
    select.value = "/types/1";
    select.dispatchEvent(new Event("change"));

    const prior = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="prior"]`
    );
    const purchase = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="purchase"]`
    );
    expect(prior.disabled).toBe(false);
    expect(purchase.disabled).toBe(true);
  });

  it("unchecks a box, which then becomes off-limits", async () => {
    const purchase = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="purchase"]`
    );
    purchase.checked = true;

    const select = requireElement<HTMLInputElement>(
      `[data-${CONTROLLER_ID}-target="type"]`
    );
    select.value = "/types/2";
    select.dispatchEvent(new Event("change"));

    expect(purchase.checked).toBe(false);
    expect(purchase.disabled).toBe(true);
  });
});
