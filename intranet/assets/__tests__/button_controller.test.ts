import { describe, expect, test, beforeEach } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import ButtonController from "../controllers/button_controller";

describe("Test ButtonController", () => {
  let application: Application;
  let form: HTMLFormElement;
  let submitBtn: HTMLButtonElement;

  beforeEach(async () => {
    document.body.innerHTML = `
      <form data-controller="button" data-action="submit->button#disable">
        <button type="submit" data-button-target="submit">Submit</button>
      </form>
    `;

    form = document.querySelector("form");
    submitBtn = document.querySelector("[type='submit']");

    application = Application.start();
    application.register("button", ButtonController);
    await Promise.resolve();
  });

  test("should disable submit button on form submit", () => {
    expect(submitBtn.disabled).toBe(false);

    form.dispatchEvent(new Event("submit"));

    expect(submitBtn.disabled).toBe(true);
  });
});
