import {
  describe,
  expect,
  test,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import LogsController from "../controllers/logs_controller";

describe("LogsController", () => {
  let app: Application;

  beforeEach(() => {
    document.body.innerHTML = `
      <turbo-frame id="tabs-turbo" data-controller="logs">
        <div class="tabs-container">
          <div class="logs-block"></div>
        </div>
      </turbo-frame>
    `;

    window.mountLogsBlocks = jest.fn();

    app = Application.start();
    app.register("logs", LogsController);
  });

  afterEach(() => {
    app.stop();
    jest.clearAllMocks();
    delete window.mountLogsBlocks;
  });

  test("mounts logs on connect", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;

    expect(window.mountLogsBlocks).toHaveBeenCalledTimes(1);
    expect(window.mountLogsBlocks).toHaveBeenCalledWith(frame);
  });

  test("mounts logs again when the same turbo frame loads", async () => {
    await Promise.resolve();

    const frame = document.querySelector("#tabs-turbo") as HTMLElement;
    frame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(window.mountLogsBlocks).toHaveBeenCalledTimes(2);
    expect(window.mountLogsBlocks).toHaveBeenLastCalledWith(frame);
  });

  test("does not mount logs when another turbo frame loads", async () => {
    await Promise.resolve();

    const otherFrame = document.createElement("turbo-frame");
    otherFrame.id = "other-frame";
    document.body.appendChild(otherFrame);

    otherFrame.dispatchEvent(new Event("turbo:frame-load", { bubbles: true }));

    expect(window.mountLogsBlocks).toHaveBeenCalledTimes(1);
  });

  test("does nothing if mountLogsBlocks is undefined", async () => {
    app.stop();
    delete window.mountLogsBlocks;

    document.body.innerHTML = `
      <turbo-frame id="tabs-turbo" data-controller="logs">
        <div class="tabs-container">
          <div class="logs-block"></div>
        </div>
      </turbo-frame>
    `;

    app = Application.start();
    app.register("logs", LogsController);

    await Promise.resolve();

    expect(window.mountLogsBlocks).toBeUndefined();
  });
});
