import {
  describe,
  expect,
  it,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import RelativeController from "../../../controllers/mis/trouble_ticket/relative_controller";

const PANEL = `
  <div
    id="tts-relative"
    data-controller="mis--trouble-ticket--relative"
    data-mis--trouble-ticket--relative-url-value="/relative"
  ></div>`;

const FIELDS = `
  <input name="trouble_ticket[module]" value="" />
  <input name="trouble_ticket[type]" value="" />`;

// The fetch -> text -> innerHTML chain needs several microtask hops to settle.
const flush = async (): Promise<void> => {
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
};

describe("RelativeController", () => {
  let application: Application;
  let mockFetch: jest.MockedFunction<typeof fetch>;

  const boot = async (html: string): Promise<void> => {
    document.body.innerHTML = html;
    application = Application.start();
    application.register("mis--trouble-ticket--relative", RelativeController);
    await Promise.resolve();
  };

  const panelEl = (): HTMLElement =>
    document.querySelector("#tts-relative") as HTMLElement;
  const moduleEl = (): HTMLInputElement =>
    document.querySelector(
      '[name="trouble_ticket[module]"]'
    ) as HTMLInputElement;
  const optionEl = (): HTMLInputElement =>
    document.querySelector('[name="trouble_ticket[type]"]') as HTMLInputElement;
  const posted = (): boolean =>
    mockFetch.mock.calls.some(
      ([, init]) => (init as RequestInit | undefined)?.method === "POST"
    );

  beforeEach(() => {
    jest.useFakeTimers();
    mockFetch = jest.fn() as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      ok: true,
      text: () => Promise.resolve("<div>panel</div>"),
    } as unknown as Response);
    global.fetch = mockFetch;
  });

  afterEach(() => {
    application?.stop();
    jest.clearAllMocks();
    jest.useRealTimers();
  });

  it("does nothing when the module/option fields are absent", async () => {
    await boot(PANEL);
    jest.advanceTimersByTime(1200);
    await flush();
    expect(mockFetch).not.toHaveBeenCalled();
  });

  it("does not load (and re-polls without refetching) while values are empty", async () => {
    await boot(FIELDS + PANEL);
    jest.advanceTimersByTime(600); // poll with unchanged empty key -> early return
    await flush();
    expect(mockFetch).not.toHaveBeenCalled();
    expect(panelEl().innerHTML).toBe("");
  });

  it("loads and injects the panel once both are set", async () => {
    await boot(FIELDS + PANEL);
    moduleEl().value = "/modules/1";
    optionEl().value = "/options/2";
    jest.advanceTimersByTime(600);
    await flush();

    expect(mockFetch).toHaveBeenCalledWith(
      "/relative?type=%2Foptions%2F2&module=%2Fmodules%2F1",
      { headers: { "X-Requested-With": "XMLHttpRequest" } }
    );
    expect(panelEl().innerHTML).toBe("<div>panel</div>");
  });

  it("clears the panel when the response is not ok", async () => {
    await boot(FIELDS + PANEL);
    mockFetch.mockResolvedValue({
      ok: false,
      text: () => Promise.resolve("ignored"),
    } as unknown as Response);
    panelEl().innerHTML = "stale";
    moduleEl().value = "/modules/1";
    optionEl().value = "/options/2";
    jest.advanceTimersByTime(600);
    await flush();
    expect(panelEl().innerHTML).toBe("");
  });

  it("clears the panel when the request rejects", async () => {
    await boot(FIELDS + PANEL);
    mockFetch.mockRejectedValue(new Error("boom"));
    panelEl().innerHTML = "stale";
    moduleEl().value = "/modules/1";
    optionEl().value = "/options/2";
    jest.advanceTimersByTime(600);
    await flush();
    expect(panelEl().innerHTML).toBe("");
  });

  it("ignores clicks that are not on a reaction button", async () => {
    await boot(FIELDS + PANEL);
    panelEl().innerHTML = "<span>no action</span>";
    (panelEl().querySelector("span") as HTMLElement).click();
    await flush();
    expect(posted()).toBe(false);
  });

  it("ignores clicks on a disabled reaction button", async () => {
    await boot(FIELDS + PANEL);
    panelEl().innerHTML =
      '<button data-action-url="/x" disabled>react</button>';
    (panelEl().querySelector("button") as HTMLButtonElement).click();
    await flush();
    expect(posted()).toBe(false);
  });

  it("re-enables the button when the action url is empty", async () => {
    await boot(FIELDS + PANEL);
    panelEl().innerHTML = '<button data-action-url="">react</button>';
    const button = panelEl().querySelector("button") as HTMLButtonElement;
    button.click();
    await flush();
    expect(button.disabled).toBe(false);
    expect(posted()).toBe(false);
  });

  it("POSTs a reaction and then reloads the panel", async () => {
    await boot(FIELDS + PANEL);
    moduleEl().value = "/modules/1";
    optionEl().value = "/options/2";
    jest.advanceTimersByTime(600);
    await flush();
    mockFetch.mockClear();

    panelEl().innerHTML =
      '<button data-action-url="/tickets/9/interested">react</button>';
    const button = panelEl().querySelector("button") as HTMLButtonElement;
    button.click();
    await flush();

    expect(button.disabled).toBe(true);
    expect(mockFetch).toHaveBeenCalledWith("/tickets/9/interested", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
  });

  it("re-enables the button when the POST fails", async () => {
    await boot(FIELDS + PANEL);
    mockFetch.mockRejectedValue(new Error("nope"));
    panelEl().innerHTML =
      '<button data-action-url="/tickets/9/interested">react</button>';
    const button = panelEl().querySelector("button") as HTMLButtonElement;
    button.click();
    await flush();
    expect(button.disabled).toBe(false);
  });
});
