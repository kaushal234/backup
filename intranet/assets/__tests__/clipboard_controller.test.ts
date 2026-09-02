import { describe, expect, jest, beforeEach } from "@jest/globals";
import { Toast } from "bootstrap";
import ClipboardController from "../controllers/clipboard_controller";

const mockToastShow = jest.fn();
const mockSuperCopied = jest.fn();

jest.mock("bootstrap", () => ({
  Toast: {
    getOrCreateInstance: jest.fn(() => ({ show: mockToastShow })),
  },
}));

jest.mock("@stimulus-components/clipboard", () => ({
  __esModule: true,
  default: class {
    copied(): void {
      mockSuperCopied();
    }
  },
}));

describe("ClipboardController", () => {
  let controller: ClipboardController;
  let element: HTMLDivElement;

  beforeEach(() => {
    jest.clearAllMocks();
    controller = new ClipboardController();
    element = document.createElement("div");
    Object.defineProperty(controller, "element", {
      value: element,
      configurable: true,
    });
  });

  describe("copied", () => {
    it("calls super.copied and shows the toast when the feedback element exists", () => {
      const feedback = document.createElement("div");
      feedback.setAttribute("data-clipboard-feedback", "");
      element.appendChild(feedback);

      controller.copied();

      expect(mockSuperCopied).toHaveBeenCalledTimes(1);
      expect(Toast.getOrCreateInstance).toHaveBeenCalledWith(feedback);
      expect(mockToastShow).toHaveBeenCalledTimes(1);
    });

    it("calls super.copied but does not show a toast when no feedback element exists", () => {
      controller.copied();

      expect(mockSuperCopied).toHaveBeenCalledTimes(1);
      expect(Toast.getOrCreateInstance).not.toHaveBeenCalled();
      expect(mockToastShow).not.toHaveBeenCalled();
    });
  });
});
