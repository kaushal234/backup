import {
  describe,
  expect,
  test,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import SubscriptionsController from "../controllers/subscriptions_controller";

describe("SubscriptionsController", () => {
  let app: Application;

  beforeEach(() => {
    document.body.innerHTML = `
      <turbo-frame id="tabs-turbo" data-controller="subscriptions">
        <div class="tabs-container">
          <div class="subscriptions"></div>
        </div>
      </turbo-frame>
    `;

    window.mountSubscriptions = jest.fn();

    app = Application.start();
    app.register("subscriptions", SubscriptionsController);
  });

  afterEach(() => {
    app.stop();
    jest.clearAllMocks();
    delete window.mountSubscriptions;
  });

  test("mounts subscriptions on connect", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;

    expect(window.mountSubscriptions).toHaveBeenCalledTimes(1);
    expect(window.mountSubscriptions).toHaveBeenCalledWith(frame);
  });

  test("mounts subscriptions again when the same turbo frame loads", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;
    frame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(window.mountSubscriptions).toHaveBeenCalledTimes(2);
    expect(window.mountSubscriptions).toHaveBeenLastCalledWith(frame);
  });

  test("does not mount subscriptions when another turbo frame loads", async () => {
    await Promise.resolve();

    const otherFrame = document.createElement("turbo-frame");
    otherFrame.id = "other-frame";
    document.body.appendChild(otherFrame);

    otherFrame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(window.mountSubscriptions).toHaveBeenCalledTimes(1);
  });

  test("does nothing if mountSubscriptions is undefined", async () => {
    app.stop();
    delete window.mountSubscriptions;

    document.body.innerHTML = `
    <turbo-frame id="tabs-turbo" data-controller="subscriptions">
      <div class="tabs-container">
        <div class="subscriptions"></div>
      </div>
    </turbo-frame>
  `;

    app = Application.start();
    app.register("subscriptions", SubscriptionsController);

    await Promise.resolve();

    expect(window.mountSubscriptions).toBeUndefined();
  });
});
