import {
  describe,
  expect,
  test,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import HincludeController from "../controllers/hinclude_controller";

describe("HincludeController", () => {
  let app: Application;

  beforeEach(() => {
    document.body.innerHTML = `
      <turbo-frame id="tabs-turbo" data-controller="hinclude">
        <div class="tabs-container"></div>
      </turbo-frame>
    `;

    globalThis.hinclude = {
      run: jest.fn(),
    };

    app = Application.start();
    app.register("hinclude", HincludeController);
  });

  afterEach(() => {
    app.stop();
    jest.clearAllMocks();
    delete globalThis.hinclude; // ✅ important
  });

  test("runs hinclude on connect", async () => {
    await Promise.resolve();

    expect(globalThis.hinclude?.run).toHaveBeenCalledTimes(1);
  });

  test("runs hinclude again when the same turbo frame loads", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;
    frame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(globalThis.hinclude?.run).toHaveBeenCalledTimes(2);
  });

  test("does not run hinclude when another turbo frame loads", async () => {
    await Promise.resolve();

    const otherFrame = document.createElement("turbo-frame");
    otherFrame.id = "other-frame";
    document.body.appendChild(otherFrame);

    otherFrame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(globalThis.hinclude?.run).toHaveBeenCalledTimes(1);
  });

  test("stops listening to turbo:frame-load on disconnect", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;
    frame.removeAttribute("data-controller");

    await Promise.resolve();

    frame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(globalThis.hinclude?.run).toHaveBeenCalledTimes(1);
  });
});

describe("HincludeController without global hinclude", () => {
  let app: Application;

  beforeEach(() => {
    document.body.innerHTML = `
      <turbo-frame id="tabs-turbo" data-controller="hinclude">
        <div class="tabs-container"></div>
      </turbo-frame>
    `;

    delete globalThis.hinclude;

    app = Application.start();
    app.register("hinclude", HincludeController);
  });

  afterEach(() => {
    app.stop();
    jest.clearAllMocks();
  });

  test("does not throw when hinclude is undefined on connect", async () => {
    await Promise.resolve();

    expect(globalThis.hinclude).toBeUndefined();
  });

  test("does not throw when hinclude is undefined on turbo:frame-load", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;

    expect(() => {
      frame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));
    }).not.toThrow();
  });
});
