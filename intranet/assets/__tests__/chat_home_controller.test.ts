import { describe, expect, jest, beforeEach, afterEach } from "@jest/globals";
import axios from "axios";
import ChatHomeController from "../controllers/chat_home_controller";
import {
  extractIdFromIri,
  redirectToConversation,
} from "../javascript/chat/chat";

jest.mock("axios");
jest.mock("../javascript/chat/chat", () => ({
  extractIdFromIri: jest.fn(),
  redirectToConversation: jest.fn(),
}));

describe("ChatHomeController", () => {
  let controller: ChatHomeController;
  let inputTarget: HTMLTextAreaElement;
  let fileTarget: { files: File[]; value: string };
  let filePreviewTarget: HTMLDivElement;
  let fileNameTarget: HTMLSpanElement;
  let preventDefault: jest.Mock;

  const mockExtractIdFromIri = extractIdFromIri as jest.MockedFunction<
    typeof extractIdFromIri
  >;
  const mockRedirectToConversation =
    redirectToConversation as jest.MockedFunction<
      typeof redirectToConversation
    >;

  beforeEach(() => {
    controller = new ChatHomeController();

    inputTarget = document.createElement("textarea");
    fileTarget = { files: [], value: "" };
    filePreviewTarget = document.createElement("div");
    filePreviewTarget.classList.add("d-none");
    fileNameTarget = document.createElement("span");
    preventDefault = jest.fn();

    Object.defineProperty(controller, "inputTarget", {
      value: inputTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "fileTarget", {
      value: fileTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "filePreviewTarget", {
      value: filePreviewTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "fileNameTarget", {
      value: fileNameTarget,
      configurable: true,
    });
    Object.defineProperty(controller, "createUrlValue", {
      value: "/conversations",
      configurable: true,
    });

    jest.clearAllMocks();
    sessionStorage.clear();
    jest.spyOn(console, "error").mockImplementation(() => {});
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it("defines the expected static targets and values", () => {
    expect(ChatHomeController.targets).toEqual([
      "input",
      "file",
      "filePreview",
      "fileName",
    ]);
    expect(ChatHomeController.values).toEqual({
      createUrl: String,
    });
  });

  describe("startConversation", () => {
    it("returns early when both input and file are empty", async () => {
      inputTarget.value = "   ";

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(preventDefault).toHaveBeenCalledTimes(1);
      expect(axios.post).not.toHaveBeenCalled();
      expect(mockExtractIdFromIri).not.toHaveBeenCalled();
      expect(mockRedirectToConversation).not.toHaveBeenCalled();
    });

    it("proceeds when only a file is provided (no text)", async () => {
      inputTarget.value = "";
      const mockFile = new File(["content"], "doc.pdf", {
        type: "application/pdf",
      });
      fileTarget.files = [mockFile];

      jest.mocked(axios.post).mockResolvedValue({
        data: { logIri: "/logs/789" },
      });
      mockExtractIdFromIri.mockReturnValue("789");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(axios.post).toHaveBeenCalledWith("/conversations");
      expect(mockRedirectToConversation).toHaveBeenCalledWith("789");
    });

    it("logs an error when create conversation request fails", async () => {
      inputTarget.value = "bonjour";

      (axios.post as jest.Mock).mockRejectedValue(new Error("HTTP error"));

      const errorSpy = jest.spyOn(console, "error");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(preventDefault).toHaveBeenCalledTimes(1);
      expect(axios.post).toHaveBeenCalledWith("/conversations");
      expect(errorSpy).toHaveBeenCalledWith("Failed to create conversation");
      expect(mockRedirectToConversation).not.toHaveBeenCalled();
    });

    it("logs an error when logIri is missing", async () => {
      inputTarget.value = "bonjour";

      jest.mocked(axios.post).mockResolvedValue({ data: {} });

      const errorSpy = jest.spyOn(console, "error");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(preventDefault).toHaveBeenCalledTimes(1);
      expect(errorSpy).toHaveBeenCalledWith("Missing logIri in response", {});
      expect(mockExtractIdFromIri).not.toHaveBeenCalled();
      expect(mockRedirectToConversation).not.toHaveBeenCalled();
    });

    it("logs an error when extractIdFromIri returns an empty string", async () => {
      inputTarget.value = "bonjour";

      jest.mocked(axios.post).mockResolvedValue({
        data: { logIri: "/logs/123" },
      });

      mockExtractIdFromIri.mockReturnValue("");

      const errorSpy = jest.spyOn(console, "error");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(mockExtractIdFromIri).toHaveBeenCalledWith("/logs/123");
      expect(errorSpy).toHaveBeenCalledWith(
        "Failed to extract id from IRI",
        "/logs/123"
      );
      expect(mockRedirectToConversation).not.toHaveBeenCalled();
    });

    it("logs an error when extractIdFromIri returns null", async () => {
      inputTarget.value = "bonjour";

      jest.mocked(axios.post).mockResolvedValue({
        data: { logIri: "/logs/456" },
      });

      mockExtractIdFromIri.mockReturnValue(null as unknown as string);

      const errorSpy = jest.spyOn(console, "error");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(mockExtractIdFromIri).toHaveBeenCalledWith("/logs/456");
      expect(errorSpy).toHaveBeenCalledWith(
        "Failed to extract id from IRI",
        "/logs/456"
      );
      expect(sessionStorage.getItem("chat:draft:456")).toBeNull();
      expect(mockRedirectToConversation).not.toHaveBeenCalled();
    });

    it("stores draft text and redirects when request succeeds", async () => {
      inputTarget.value = "salut";

      jest.mocked(axios.post).mockResolvedValue({
        data: { logIri: "/logs/789" },
      });

      mockExtractIdFromIri.mockReturnValue("789");

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(axios.post).toHaveBeenCalledWith("/conversations");
      expect(mockExtractIdFromIri).toHaveBeenCalledWith("/logs/789");
      expect(sessionStorage.getItem("chat:draft:789")).toBe("salut");
      expect(sessionStorage.getItem("chat:draft-file:789")).toBeNull();
      expect(mockRedirectToConversation).toHaveBeenCalledWith("789");
    });

    it("stores the file as base64 in sessionStorage when a file is attached", async () => {
      inputTarget.value = "see file";
      const mockFile = new File(["hello"], "test.txt", { type: "text/plain" });
      fileTarget.files = [mockFile];

      jest.mocked(axios.post).mockResolvedValue({
        data: { logIri: "/logs/789" },
      });
      mockExtractIdFromIri.mockReturnValue("789");

      // Mock FileReader to control the async base64 conversion
      const mockBase64 = "data:text/plain;base64,aGVsbG8=";
      const readerInstance = {
        result: mockBase64,
        onload: null as (() => void) | null,
        onerror: null as (() => void) | null,
        readAsDataURL: jest.fn().mockImplementation(() => {
          Promise.resolve().then(() => readerInstance.onload?.());
        }),
      };
      (global as any).FileReader = jest.fn(() => readerInstance);

      await controller.startConversation({
        preventDefault,
      } as unknown as Event);

      expect(sessionStorage.getItem("chat:draft:789")).toBe("see file");

      const draftFileJson = sessionStorage.getItem("chat:draft-file:789");
      expect(draftFileJson).not.toBeNull();
      const draftFile = JSON.parse(draftFileJson ?? "") as {
        name: string;
        type: string;
        data: string;
      };
      expect(draftFile.name).toBe("test.txt");
      expect(draftFile.type).toBe("text/plain");
      expect(draftFile.data).toBe(mockBase64);
      expect(mockRedirectToConversation).toHaveBeenCalledWith("789");
    });
  });

  describe("fileChanged", () => {
    it("shows the file preview and sets the file name when a file is selected", () => {
      const mockFile = new File(["content"], "report.pdf", {
        type: "application/pdf",
      });
      fileTarget.files = [mockFile];
      filePreviewTarget.classList.add("d-none");

      controller.fileChanged();

      expect(fileNameTarget.textContent).toBe("report.pdf");
      expect(filePreviewTarget.classList.contains("d-none")).toBe(false);
    });

    it("hides the preview when no file is selected", () => {
      filePreviewTarget.classList.remove("d-none");
      fileTarget.files = [];

      controller.fileChanged();

      expect(filePreviewTarget.classList.contains("d-none")).toBe(true);
    });
  });

  describe("removeFile", () => {
    it("clears the file input value and hides the preview", () => {
      filePreviewTarget.classList.remove("d-none");
      fileTarget.value = "C:\\fakepath\\doc.pdf";

      controller.removeFile();

      expect(fileTarget.value).toBe("");
      expect(filePreviewTarget.classList.contains("d-none")).toBe(true);
    });
  });

  describe("autoResize", () => {
    it("sets textarea height to auto then to its scrollHeight", () => {
      Object.defineProperty(inputTarget, "scrollHeight", { value: 80 });

      controller.autoResize();

      expect(inputTarget.style.height).toBe("80px");
    });
  });

  describe("handleKeydown", () => {
    it("starts conversation on Enter without Shift", () => {
      const startConversationSpy = jest
        .spyOn(controller, "startConversation")
        .mockResolvedValue();

      const event = {
        key: "Enter",
        shiftKey: false,
        preventDefault,
      } as unknown as KeyboardEvent;

      controller.handleKeydown(event);

      expect(preventDefault).toHaveBeenCalledTimes(1);
      expect(startConversationSpy).toHaveBeenCalledTimes(1);
      expect(startConversationSpy).toHaveBeenCalledWith(event);
    });

    it("does not start conversation on Enter with Shift", () => {
      const startConversationSpy = jest
        .spyOn(controller, "startConversation")
        .mockResolvedValue();

      const event = {
        key: "Enter",
        shiftKey: true,
        preventDefault,
      } as unknown as KeyboardEvent;

      controller.handleKeydown(event);

      expect(preventDefault).not.toHaveBeenCalled();
      expect(startConversationSpy).not.toHaveBeenCalled();
    });

    it("does not start conversation on other keys", () => {
      const startConversationSpy = jest
        .spyOn(controller, "startConversation")
        .mockResolvedValue();

      const event = {
        key: "Escape",
        shiftKey: false,
        preventDefault,
      } as unknown as KeyboardEvent;

      controller.handleKeydown(event);

      expect(preventDefault).not.toHaveBeenCalled();
      expect(startConversationSpy).not.toHaveBeenCalled();
    });

    it("handles startConversation rejection without throwing", async () => {
      const error = new Error("failed");
      jest.spyOn(controller, "startConversation").mockRejectedValue(error);
      const consoleErrorSpy = jest.spyOn(console, "error");

      const event = {
        key: "Enter",
        shiftKey: false,
        preventDefault,
      } as unknown as KeyboardEvent;

      controller.handleKeydown(event);

      // Flush the rejected promise
      await Promise.resolve();
      await Promise.resolve();

      expect(consoleErrorSpy).toHaveBeenCalledWith(
        "Failed to start conversation",
        error
      );
    });
  });
});
