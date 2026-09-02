import { describe, expect, jest } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import DatepickerController from "../controllers/datepicker_controller";

const mockDispose = jest.fn();
const mockAddEventListener = jest.fn();

jest.mock("@eonasdan/tempus-dominus", () => {
  return {
    TempusDominus: jest.fn().mockImplementation(() => ({
      dispose: mockDispose,
      element: {
        addEventListener: mockAddEventListener,
      },
    })),
  };
});

describe("DatepickerController", () => {
  let application: Application;
  let element: HTMLInputElement;

  beforeEach(() => {
    document.body.innerHTML = `<input data-controller="datepicker" data-options='{"someOption":true}'></input>`;
    element = document.querySelector("input") as HTMLInputElement;

    application = Application.start();
    application.register("datepicker", DatepickerController);
  });

  afterEach(() => {
    jest.clearAllMocks();
    application.stop();
  });

  it("should initialize TempusDominus on connect", () => {
    const controller = application.getControllerForElementAndIdentifier(
      element,
      "datepicker"
    ) as DatepickerController;

    expect(controller).toBeDefined();
    expect((controller as any).datepicker).toBeDefined();
    expect((element as any).tempusDominusInstance).toBe(
      (controller as any).datepicker
    );
  });

  it("should dispose TempusDominus on disconnect", () => {
    const controller = application.getControllerForElementAndIdentifier(
      element,
      "datepicker"
    ) as DatepickerController;

    controller.disconnect();

    expect(mockDispose).toHaveBeenCalled();
  });
});

describe("DatepickerController without options", () => {
  let application: Application;
  let element: HTMLInputElement;

  beforeEach(() => {
    document.body.innerHTML = `<input data-controller="datepicker"></input>`;
    element = document.querySelector("input") as HTMLInputElement;

    application = Application.start();
    application.register("datepicker", DatepickerController);
  });

  afterEach(() => {
    jest.clearAllMocks();
    application.stop();
  });

  it("should initialize TempusDominus on connect without options", () => {
    const controller = application.getControllerForElementAndIdentifier(
      element,
      "datepicker"
    ) as DatepickerController;

    expect(controller).toBeDefined();
    expect((controller as any).datepicker).toBeDefined();
    expect((element as any).tempusDominusInstance).toBe(
      (controller as any).datepicker
    );
  });
  it("should not throw on disconnect when TempusDominus is not initialized", () => {
    const controller = application.getControllerForElementAndIdentifier(
      element,
      "datepicker"
    ) as DatepickerController;

    (controller as any).datepicker = undefined;

    expect(() => controller.disconnect()).not.toThrow();
    expect(mockDispose).not.toHaveBeenCalled();
  });
});
