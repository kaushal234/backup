import { describe, expect, jest, beforeEach, afterEach } from "@jest/globals";
import AiServiceModalController from "../controllers/ai_service_modal_controller";
import { renderMarkdown } from "../controllers/utils/render_markdown";

jest.mock("../controllers/utils/render_markdown", () => ({
  renderMarkdown: jest.fn(),
}));

describe("AiServiceModalController", () => {
  let controller: AiServiceModalController;
  let element: HTMLDivElement;
  let messageTarget: HTMLDivElement;
  let certifyTarget: HTMLInputElement;
  let confirmTarget: HTMLButtonElement;

  const mockRenderMarkdown = renderMarkdown as jest.MockedFunction<
    typeof renderMarkdown
  >;

  beforeEach(() => {
    controller = new AiServiceModalController();

    element = document.createElement("div");
    messageTarget = document.createElement("div");
    messageTarget.dataset.message = "**hello** service";
    certifyTarget = document.createElement("input");
    certifyTarget.type = "checkbox";
    confirmTarget = document.createElement("button");
    confirmTarget.disabled = true;

    Object.defineProperty(controller, "element", {
      value: element,
      configurable: true,
    });
    Object.defineProperty(controller, "messageTarget", {
      value: messageTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "certifyTarget", {
      value: certifyTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "confirmTarget", {
      value: confirmTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "urlValue", {
      value: "https://copilot.microsoft.com/",
      configurable: true,
    });

    jest.clearAllMocks();
    mockRenderMarkdown.mockReturnValue("<p><strong>hello</strong> service</p>");
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it("defines the expected static targets and values", () => {
    expect(AiServiceModalController.targets).toEqual([
      "message",
      "certify",
      "confirm",
    ]);
    expect(AiServiceModalController.values).toEqual({
      url: String,
    });
  });

  describe("connect", () => {
    it("renders the markdown message into the message target", () => {
      controller.connect();

      expect(mockRenderMarkdown).toHaveBeenCalledWith("**hello** service");
      expect(messageTarget.innerHTML).toBe(
        "<p><strong>hello</strong> service</p>"
      );
    });

    it("falls back to an empty string when no message dataset is set", () => {
      delete messageTarget.dataset.message;

      controller.connect();

      expect(mockRenderMarkdown).toHaveBeenCalledWith("");
    });

    it("resets the modal when it is hidden", () => {
      controller.connect();
      certifyTarget.checked = true;
      confirmTarget.disabled = false;

      element.dispatchEvent(new Event("hidden.bs.modal"));

      expect(certifyTarget.checked).toBe(false);
      expect(confirmTarget.disabled).toBe(true);
    });
  });

  describe("disconnect", () => {
    it("removes the hidden.bs.modal listener", () => {
      controller.connect();
      controller.disconnect();

      certifyTarget.checked = true;
      confirmTarget.disabled = false;
      element.dispatchEvent(new Event("hidden.bs.modal"));

      expect(certifyTarget.checked).toBe(true);
      expect(confirmTarget.disabled).toBe(false);
    });
  });

  describe("toggle", () => {
    it("enables the confirm button when certify is checked", () => {
      certifyTarget.checked = true;

      controller.toggle();

      expect(confirmTarget.disabled).toBe(false);
    });

    it("disables the confirm button when certify is unchecked", () => {
      certifyTarget.checked = false;
      confirmTarget.disabled = false;

      controller.toggle();

      expect(confirmTarget.disabled).toBe(true);
    });
  });

  describe("open", () => {
    it("opens the configured service url in a new tab", () => {
      const openSpy = jest.spyOn(window, "open").mockImplementation(() => null);

      controller.open();

      expect(openSpy).toHaveBeenCalledWith(
        "https://copilot.microsoft.com/",
        "_blank",
        "noopener,noreferrer"
      );
    });
  });
});
