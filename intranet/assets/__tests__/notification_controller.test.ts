import {
  describe,
  expect,
  jest,
  beforeEach,
  afterEach,
  it,
} from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import NotificationController from "../controllers/notification_controller";

const mockFetch = jest.fn() as jest.MockedFunction<typeof fetch>;
global.fetch = mockFetch;

const mockRouting = {
  generate: jest.fn((route: string, params?: Record<string, unknown>) => {
    if (params?.id)
      return `/en/private/${route.replace(/_ajax$/, "").replace(/_/g, "-")}/${
        params.id
      }`;
    return `/en/private/${route.replace(/_ajax$/, "").replace(/_/g, "-")}`;
  }),
};
(global as unknown as { Routing: typeof mockRouting }).Routing = mockRouting;

const mockEmptyResponse = () => Promise.resolve({} as unknown as Response);

const makeNotification = (overrides = {}) => ({
  id: 1,
  unread: true,
  createdAt: new Date().toISOString(),
  textDisplayed: "TTS#12345: This TTS has been closed as SOLVED.",
  url: "https://example.com/ticket/1",
  template: {
    module: {
      name: "TTS",
      notificationColor: "#004F9E",
    },
  },
  ...overrides,
});

const waitForController = async () => {
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
  await Promise.resolve();
};

describe("NotificationController", () => {
  let application: Application;

  const setupDom = () => {
    document.body.innerHTML = `
            <div data-controller="notification">
                <span data-notification-target="badge">
                    <span data-notification-target="count"></span>
                </span>
                <ul id="notifications" data-notification-target="list">
                    <li>
                        <a data-action="click->notification#clearAll">Clear all</a>
                        <a data-action="click->notification#readAll">Read all</a>
                    </li>
                </ul>
            </div>
        `;
  };

  const startApp = async (notifications: unknown[] = []) => {
    mockFetch.mockResolvedValueOnce({
      json: async () => notifications,
    } as unknown as Response);

    setupDom();
    application = Application.start();
    application.register("notification", NotificationController);
    await waitForController();
  };

  beforeEach(() => {
    jest.clearAllMocks();
  });

  afterEach(() => {
    application?.stop();
    document.body.innerHTML = "";
  });

  describe("loadNotifications", () => {
    it("should fetch notifications on connect", async () => {
      await startApp([]);
      expect(mockFetch).toHaveBeenCalledTimes(1);
      expect(mockRouting.generate).toHaveBeenCalledWith("notifications_ajax");
    });

    it("should render a notification line per notification", async () => {
      await startApp([
        makeNotification({ id: 1 }),
        makeNotification({ id: 2 }),
      ]);
      const lines = document.querySelectorAll(".notification-line");
      expect(lines).toHaveLength(2);
    });

    it("should display unread count when there are unread notifications", async () => {
      await startApp([
        makeNotification({ id: 1, unread: true }),
        makeNotification({ id: 2, unread: true }),
        makeNotification({ id: 3, unread: false }),
      ]);
      const count = document.querySelector(
        "[data-notification-target='count']"
      );
      expect(count?.textContent).toBe("2");
    });

    it("should not display count when all notifications are read", async () => {
      await startApp([makeNotification({ id: 1, unread: false })]);
      const count = document.querySelector(
        "[data-notification-target='count']"
      );
      expect(count?.textContent).toBe("");
    });

    it("should add unread class on unread notification lines", async () => {
      await startApp([makeNotification({ id: 1, unread: true })]);
      const line = document.getElementById("notification-line-1");
      expect(line?.classList.contains("unread")).toBe(true);
    });

    it("should not add unread class on read notification lines", async () => {
      await startApp([makeNotification({ id: 1, unread: false })]);
      const line = document.getElementById("notification-line-1");
      expect(line?.classList.contains("unread")).toBe(false);
    });

    it("should not crash when api returns empty array", async () => {
      await startApp([]);
      const lines = document.querySelectorAll(".notification-line");
      expect(lines).toHaveLength(0);
    });

    it("should not crash when api fails", async () => {
      const consoleSpy = jest
        .spyOn(console, "error")
        .mockImplementation(() => {});
      mockFetch.mockRejectedValueOnce(new Error("Network error"));
      setupDom();
      application = Application.start();
      application.register("notification", NotificationController);
      await waitForController();
      expect(consoleSpy).toHaveBeenCalledWith(
        "Erreur chargement notifications",
        expect.any(Error)
      );
      consoleSpy.mockRestore();
    });
  });

  describe("appendNotification", () => {
    it("should render the notification text in a link", async () => {
      await startApp([
        makeNotification({ id: 1, textDisplayed: "TTS#12345: closed." }),
      ]);
      const link = document.querySelector("#notification-line-1 a");
      expect(link?.textContent).toContain("TTS#12345: closed.");
    });

    it("should render the module badge", async () => {
      await startApp([makeNotification({ id: 1 })]);
      const badge = document.querySelector("#notification-line-1 .badge");
      expect(badge?.textContent?.trim()).toBe("TTS");
    });

    it("should render a remove button with correct data-id", async () => {
      await startApp([makeNotification({ id: 1 })]);
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove']"
      );
      expect(removeBtn).not.toBeNull();
      expect(removeBtn?.getAttribute("data-id")).toBe("1");
    });
  });

  describe("remove", () => {
    beforeEach(async () => {
      await startApp([
        makeNotification({ id: 1, unread: true }),
        makeNotification({ id: 2, unread: false }),
      ]);
      mockFetch.mockImplementation(mockEmptyResponse);
    });

    it("should call remove route with correct id", () => {
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove'][data-id='1']"
      ) as HTMLElement;
      removeBtn.click();
      expect(mockFetch).toHaveBeenCalledWith(expect.stringContaining("1"));
    });

    it("should remove the notification line from DOM", () => {
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove'][data-id='1']"
      ) as HTMLElement;
      removeBtn.click();
      expect(document.getElementById("notification-line-1")).toBeNull();
    });

    it("should decrement count when removing an unread notification", () => {
      const count = document.querySelector(
        "[data-notification-target='count']"
      ) as HTMLElement;
      count.textContent = "1";
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove'][data-id='1']"
      ) as HTMLElement;
      removeBtn.click();
      expect(count.textContent).toBe("");
    });

    it("should hide badge when count reaches 0", () => {
      const count = document.querySelector(
        "[data-notification-target='count']"
      ) as HTMLElement;
      count.textContent = "1";
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove'][data-id='1']"
      ) as HTMLElement;
      removeBtn.click();
      const badge = document.querySelector(
        "[data-notification-target='badge']"
      ) as HTMLElement;
      expect(badge.classList.contains("d-none")).toBe(true);
    });

    it("should not decrement count when removing a read notification", () => {
      const count = document.querySelector(
        "[data-notification-target='count']"
      ) as HTMLElement;
      count.textContent = "1";
      const removeBtn = document.querySelector(
        "[data-action='click->notification#remove'][data-id='2']"
      ) as HTMLElement;
      removeBtn.click();
      expect(count.textContent).toBe("1");
    });
  });

  describe("read", () => {
    beforeEach(async () => {
      await startApp([makeNotification({ id: 1 })]);
      mockFetch.mockImplementation(mockEmptyResponse);
    });

    it("should call read route with correct id", () => {
      const link = document.querySelector(
        "[data-action='click->notification#read']"
      ) as HTMLElement;
      link.click();
      expect(mockFetch).toHaveBeenCalledWith(expect.stringContaining("1"));
    });
  });

  describe("readAll", () => {
    beforeEach(async () => {
      await startApp([
        makeNotification({ id: 1, unread: true }),
        makeNotification({ id: 2, unread: true }),
      ]);
      mockFetch.mockImplementation(mockEmptyResponse);
    });

    it("should call read all route", () => {
      const btn = document.querySelector(
        "[data-action='click->notification#readAll']"
      ) as HTMLElement;
      btn.click();
      expect(mockRouting.generate).toHaveBeenCalledWith(
        "read_all_notifications_ajax"
      );
    });

    it("should remove unread class from all notification lines", () => {
      const btn = document.querySelector(
        "[data-action='click->notification#readAll']"
      ) as HTMLElement;
      btn.click();
      document.querySelectorAll(".notification-line").forEach((el) => {
        expect(el.classList.contains("unread")).toBe(false);
      });
    });

    it("should clear count and hide badge", () => {
      const count = document.querySelector(
        "[data-notification-target='count']"
      ) as HTMLElement;
      count.textContent = "2";
      const btn = document.querySelector(
        "[data-action='click->notification#readAll']"
      ) as HTMLElement;
      btn.click();
      expect(count.textContent).toBe("");
      const badge = document.querySelector(
        "[data-notification-target='badge']"
      ) as HTMLElement;
      expect(badge.classList.contains("d-none")).toBe(true);
    });
  });

  describe("clearAll", () => {
    beforeEach(async () => {
      await startApp([
        makeNotification({ id: 1 }),
        makeNotification({ id: 2 }),
      ]);
      mockFetch.mockImplementation(mockEmptyResponse);
    });

    it("should call delete all route", () => {
      const btn = document.querySelector(
        "[data-action='click->notification#clearAll']"
      ) as HTMLElement;
      btn.click();
      expect(mockRouting.generate).toHaveBeenCalledWith(
        "delete_all_notifications_ajax"
      );
    });

    it("should remove all notification lines from DOM", () => {
      const btn = document.querySelector(
        "[data-action='click->notification#clearAll']"
      ) as HTMLElement;
      btn.click();
      expect(document.querySelectorAll(".notification-line")).toHaveLength(0);
    });

    it("should clear count and hide badge", () => {
      const count = document.querySelector(
        "[data-notification-target='count']"
      ) as HTMLElement;
      count.textContent = "2";
      const btn = document.querySelector(
        "[data-action='click->notification#clearAll']"
      ) as HTMLElement;
      btn.click();
      expect(count.textContent).toBe("");
      const badge = document.querySelector(
        "[data-notification-target='badge']"
      ) as HTMLElement;
      expect(badge.classList.contains("d-none")).toBe(true);
    });
  });
});
