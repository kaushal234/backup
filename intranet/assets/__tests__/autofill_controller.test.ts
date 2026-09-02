import { describe, expect } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import AutofillController from "../controllers/autofill_controller";

describe("AutofillController", () => {
  let application: Application;
  let target: HTMLInputElement;
  let target2: HTMLInputElement;
  let button: HTMLButtonElement;

  beforeEach(() => {
    document.body.innerHTML = `
      <div data-controller="autofill" data-autofill-target-value="target-input">
        <input data-autofill-target="source" value="06/03/2026">
        <button type="button" data-action="autofill#autofill"></button>
      </div>
      
      <input id="target" data-autofill-target="target-input">
      <input id="target2" data-autofill-target="target-input">
    `;
    target = document.querySelector("input#target") as HTMLInputElement;
    target2 = document.querySelector("input#target2") as HTMLInputElement;
    button = document.querySelector("button") as HTMLButtonElement;

    application = Application.start();
    application.register("autofill", AutofillController);
  });

  it("should autofill target input", () => {
    button.click();

    expect(target.value).toBe("06/03/2026");
    expect(target2.value).toBe("06/03/2026");
  });
});
