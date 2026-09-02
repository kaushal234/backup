import { describe, expect, jest } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import DatepickerTargetMinDateController from "../controllers/datepicker_target_min_date_controller";

const mockUpdateOptions = jest.fn();

jest.mock("@eonasdan/tempus-dominus", () => {
  return {
    TempusDominus: jest.fn().mockImplementation(() => ({
      updateOptions: mockUpdateOptions,
    })),
  };
});

describe("DatepickerTargetMinDateController", () => {
  let application: Application;
  let element: HTMLInputElement;
  let targetElement: HTMLInputElement;

  beforeEach(() => {
    document.body.innerHTML = `<input 
      id="source"
      data-controller="datepicker-target-min-date"
      data-datepicker-target-min-date-target-value="target"
      data-options='{"someOption":true}'
    />
    <input id="target" data-controller="datepicker" data-options='{"someOption":true}' />
    `;
    element = document.querySelector("input#source") as HTMLInputElement;
    targetElement = document.querySelector("input#target") as HTMLInputElement;

    (targetElement as any).tempusDominusInstance = {
      updateOptions: mockUpdateOptions,
    };

    application = Application.start();
    application.register(
      "datepicker-target-min-date",
      DatepickerTargetMinDateController
    );
  });

  afterEach(() => {
    jest.clearAllMocks();
    application.stop();
  });

  it("should call update options of tempus dominus", () => {
    const controller = application.getControllerForElementAndIdentifier(
      element,
      "datepicker-target-min-date"
    ) as DatepickerTargetMinDateController;

    const date = new Date("2026-03-01");

    const event = new CustomEvent("change.td", {
      detail: { date },
    });
    element.dispatchEvent(event);

    expect(controller).toBeDefined();
    expect((element as any).tempusDominusInstance).toBe(
      (controller as any).datepicker
    );
    expect(mockUpdateOptions).toHaveBeenCalledWith({
      restrictions: { minDate: date },
    });
  });
});
