import {
  describe,
  expect,
  test,
  beforeEach,
  afterEach,
  jest,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import RemotePreviewHoverController from "../controllers/remote_preview_hover_controller";

const mockPopperUpdate = jest.fn();
const mockPopperDestroy = jest.fn();
const mockCreatePopper = jest.fn((...args: unknown[]) => ({
  update: mockPopperUpdate,
  destroy: mockPopperDestroy,
  args,
}));

jest.mock("@popperjs/core", () => ({
  createPopper: (...args: unknown[]) => mockCreatePopper(...args),
}));

const mockResizeObserverObserve = jest.fn();
const mockResizeObserverDisconnect = jest.fn();
let resizeObserverCallbacks: ResizeObserverCallback[] = [];

global.ResizeObserver = class implements ResizeObserver {
  constructor(callback: ResizeObserverCallback) {
    resizeObserverCallbacks.push(callback);
  }

  observe = mockResizeObserverObserve;

  unobserve = jest.fn();

  disconnect = mockResizeObserverDisconnect;
};

const nextTick = async () => {
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
};

const rowHtml = (id: string): string => `
  <div id="${id}" data-controller="remote-preview-hover">
    <span
      data-remote-preview-hover-target="trigger"
      data-action="mouseenter->remote-preview-hover#open"
    >icon</span>
    <div data-remote-preview-hover-target="box">
      <button type="button" data-action="click->remote-preview-hover#close">close</button>
      <div class="inside-content">content</div>
    </div>
  </div>
`;

describe("RemotePreviewHoverController", () => {
  let application: Application;

  const mount = async (rows: string[]) => {
    document.body.innerHTML = rows.map(rowHtml).join("");
    application = Application.start();
    application.register("remote-preview-hover", RemotePreviewHoverController);
    await nextTick();
  };

  const getRow = (id: string) => document.getElementById(id) as HTMLElement;
  const getTrigger = (id: string) =>
    getRow(id).querySelector(
      "[data-remote-preview-hover-target='trigger']"
    ) as HTMLElement;
  const getBox = (id: string) =>
    getRow(id).querySelector(
      "[data-remote-preview-hover-target='box']"
    ) as HTMLElement;
  const getCloseButton = (id: string) =>
    getRow(id).querySelector("button") as HTMLElement;
  const getInsideContent = (id: string) =>
    getBox(id).querySelector(".inside-content") as HTMLElement;

  const open = async (id: string) => {
    getTrigger(id).dispatchEvent(
      new MouseEvent("mouseenter", { bubbles: true })
    );
    await nextTick();
  };

  beforeEach(() => {
    resizeObserverCallbacks = [];
  });

  // Flush the controller's state (listeners, Popper, etc...) after each test to avoid polluting the next one
  afterEach(async () => {
    // Removes elements from DOM to trigger the MutationObserver of Stimulus
    document.body.innerHTML = "";
    // As the MutationObserver's callback is async, waiting for the nextTick is needed
    await nextTick();
    application?.stop();
  });

  test("open() adds the show class and creates a popper anchored on the trigger and box", async () => {
    await mount(["row-0"]);
    await open("row-0");

    expect(getBox("row-0").classList.contains("show")).toBe(true);
    expect(mockCreatePopper).toHaveBeenCalledWith(
      getTrigger("row-0"),
      getBox("row-0"),
      expect.objectContaining({ strategy: "fixed", placement: "left" })
    );
  });

  test("open() observes the box so a resize repositions the popper", async () => {
    await mount(["row-0"]);
    await open("row-0");

    expect(mockResizeObserverObserve).toHaveBeenCalledWith(getBox("row-0"));

    resizeObserverCallbacks[resizeObserverCallbacks.length - 1](
      [],
      {} as ResizeObserver
    );

    expect(mockPopperUpdate).toHaveBeenCalled();
  });

  test("close() via the close button removes the show class and tears down the popper", async () => {
    await mount(["row-0"]);
    await open("row-0");

    getCloseButton("row-0").click();
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(false);
    expect(mockPopperDestroy).toHaveBeenCalled();
    expect(mockResizeObserverDisconnect).toHaveBeenCalled();
  });

  test("Escape closes the open preview and is a no-op on already-closed ones", async () => {
    await mount(["row-0", "row-1"]);
    await open("row-0");

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(false);
    expect(getBox("row-1").classList.contains("show")).toBe(false);
  });

  test("a non-Escape key does not close the open preview", async () => {
    await mount(["row-0"]);
    await open("row-0");

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "a" }));
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(true);
  });

  test("a click outside the component closes the open preview", async () => {
    await mount(["row-0"]);
    await open("row-0");

    document.dispatchEvent(new MouseEvent("click", { bubbles: true }));
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(false);
  });

  test("a click inside the tooltip does not close it", async () => {
    await mount(["row-0"]);
    await open("row-0");

    getInsideContent("row-0").dispatchEvent(
      new MouseEvent("click", { bubbles: true })
    );
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(true);
  });

  test("opening a second row closes the previously open one", async () => {
    await mount(["row-0", "row-1"]);
    await open("row-0");
    await open("row-1");

    expect(getBox("row-0").classList.contains("show")).toBe(false);
    expect(getBox("row-1").classList.contains("show")).toBe(true);
  });

  test("re-opening the already-open row does not try to close itself, and refreshes the popper", async () => {
    await mount(["row-0"]);
    await open("row-0");
    await open("row-0");

    expect(getBox("row-0").classList.contains("show")).toBe(true);
    expect(mockCreatePopper).toHaveBeenCalledTimes(2);
    // Destroyed once: the first open() has nothing to destroy but, the second one destroys the first before mounting.
    expect(mockPopperDestroy).toHaveBeenCalledTimes(1);
  });

  test("disconnecting the currently open row does not break opening another one afterwards", async () => {
    await mount(["row-0", "row-1"]);
    await open("row-0");

    getRow("row-0").remove();
    await nextTick();

    await open("row-1");

    expect(getBox("row-1").classList.contains("show")).toBe(true);
  });

  test("disconnecting a row that isn't the open one leaves the open one untouched", async () => {
    await mount(["row-0", "row-1"]);
    await open("row-0");

    getRow("row-1").remove();
    await nextTick();

    expect(getBox("row-0").classList.contains("show")).toBe(true);
  });

  test("disconnecting before ever opening does not throw and does not touch the popper", async () => {
    await mount(["row-0"]);

    expect(() => getRow("row-0").remove()).not.toThrow();
    await nextTick();

    expect(mockPopperDestroy).not.toHaveBeenCalled();
  });
});
