// eslint-disable-next-line max-classes-per-file
import { describe, expect, jest, beforeEach, afterEach } from "@jest/globals";
import axios from "axios";
import { EventSourcePolyfill } from "event-source-polyfill";
import ChatController from "../controllers/chat_controller";
import {
  extractIdFromIri,
  renderAssistantBubble,
} from "../javascript/chat/chat";
import { renderMarkdown } from "../controllers/utils/render_markdown";

jest.mock("axios");
jest.mock("@hotwired/stimulus", () => ({
  Controller: class {},
}));

const mockModalHide = jest.fn();
jest.mock("bootstrap", () => ({
  Modal: {
    getOrCreateInstance: jest.fn(() => ({ hide: mockModalHide })),
  },
}));

jest.mock("bazinga-translator", () => ({
  trans: jest.fn((key: string) => key),
}));

jest.mock("../javascript/chat/chat", () => ({
  extractIdFromIri: jest.fn(),
  renderAssistantBubble: jest.fn(),
}));

jest.mock("../controllers/utils/render_markdown", () => ({
  renderMarkdown: jest.fn((content: string) => content),
}));

type ListenerMap = Record<string, Array<(event: MessageEvent) => void>>;

class MockEventSourcePolyfill {
  static instances: MockEventSourcePolyfill[] = [];

  public url: string;

  public options: unknown;

  public listeners: ListenerMap = {};

  public onerror: ((event: Event) => void) | null = null;

  public close = jest.fn();

  constructor(url: string, options: unknown) {
    this.url = url;
    this.options = options;
    MockEventSourcePolyfill.instances.push(this);
  }

  addEventListener(
    type: string,
    callback: (event: MessageEvent) => void
  ): void {
    if (!this.listeners[type]) {
      this.listeners[type] = [];
    }

    this.listeners[type].push(callback);
  }

  emit(type: string, data: unknown): void {
    const event = { data } as MessageEvent;
    (this.listeners[type] ?? []).forEach((callback) => callback(event));
  }
}

jest.mock("event-source-polyfill", () => ({
  EventSourcePolyfill: jest.fn((url: string, options: unknown) => {
    return new MockEventSourcePolyfill(url, options);
  }),
}));

describe("ChatController", () => {
  let controller: ChatController;
  let messagesTarget: HTMLDivElement;
  let inputTarget: HTMLTextAreaElement;
  let filePreviewTarget: HTMLDivElement;
  let fileNameTarget: HTMLSpanElement;
  let rateButtonTarget: HTMLButtonElement;
  let ratingCommentTarget: HTMLTextAreaElement;
  let ratingErrorTarget: HTMLDivElement;

  const mockedExtractIdFromIri = extractIdFromIri as jest.MockedFunction<
    typeof extractIdFromIri
  >;
  const mockedRenderAssistantBubble =
    renderAssistantBubble as jest.MockedFunction<typeof renderAssistantBubble>;
  const mockedRenderMarkdown = renderMarkdown as jest.MockedFunction<
    typeof renderMarkdown
  >;
  const mockedEventSourcePolyfill = EventSourcePolyfill as jest.Mock;

  function buildController(): ChatController {
    const instance = new ChatController() as ChatController &
      Record<string, any>;

    messagesTarget = document.createElement("div");
    Object.defineProperty(messagesTarget, "scrollHeight", {
      configurable: true,
      get: () => 999,
    });

    inputTarget = document.createElement("textarea");
    filePreviewTarget = document.createElement("div");
    filePreviewTarget.classList.add("d-none");
    fileNameTarget = document.createElement("span");

    instance.messagesTarget = messagesTarget;
    instance.inputTarget = inputTarget;
    instance.fileTarget = document.createElement("input");
    instance.filePreviewTarget = filePreviewTarget;
    instance.fileNameTarget = fileNameTarget;
    instance.sendUrlValue = "/send";
    instance.rateUrlValue = "/rate";
    instance.logIriValue = "/logs/123";
    instance.logTokenValue = "secret-token";
    instance.mercureUrlValue = "https://mercure.example/.well-known/mercure";

    instance.element = document.createElement("div");

    rateButtonTarget = document.createElement("button");
    rateButtonTarget.classList.add("d-none");
    instance.rateButtonTarget = rateButtonTarget;
    instance.hasRateButtonTarget = true;

    ratingCommentTarget = document.createElement("textarea");
    instance.ratingCommentTarget = ratingCommentTarget;
    instance.hasRatingCommentTarget = true;

    ratingErrorTarget = document.createElement("div");
    ratingErrorTarget.classList.add("d-none");
    instance.ratingErrorTarget = ratingErrorTarget;
    instance.hasRatingErrorTarget = true;

    return instance;
  }

  beforeEach(() => {
    jest.clearAllMocks();

    // Run rAF callbacks synchronously so tests can assert DOM state immediately.
    // NOTE: cb() runs before the return value is assigned, so the controller uses a
    // boolean flag (renderPending) rather than the frame ID — this mock is safe.
    global.requestAnimationFrame = (cb: FrameRequestCallback): number => {
      cb(0);
      return 0;
    };

    MockEventSourcePolyfill.instances = [];
    controller = buildController();

    sessionStorage.clear();
  });

  afterEach(() => {});

  it("connect calls expected methods and handles restoreDraftAndSend error", async () => {
    const error = new Error("restore failed");
    const renderAssistantSpy = jest
      .spyOn(controller, "renderExistingAssistantMessages")
      .mockImplementation();
    const subscribeSpy = jest
      .spyOn(controller, "subscribe")
      .mockImplementation();
    const restoreSpy = jest
      .spyOn(controller, "restoreDraftAndSend")
      .mockRejectedValue(error);
    const scrollSpy = jest
      .spyOn(controller, "scrollToBottom")
      .mockImplementation();
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();

    controller.connect();
    await Promise.resolve();

    expect(renderAssistantSpy).toHaveBeenCalledTimes(1);
    expect(subscribeSpy).toHaveBeenCalledTimes(1);
    expect(restoreSpy).toHaveBeenCalledTimes(1);
    expect(scrollSpy).toHaveBeenCalledTimes(1);
    expect(consoleErrorSpy).toHaveBeenCalledWith(
      "Failed to restore draft",
      error
    );
  });

  it("disconnect closes the subscription", () => {
    const close = jest.fn();

    (controller as any).eventSource = { close };
    controller.disconnect();

    expect(close).toHaveBeenCalledTimes(1);
    expect((controller as any).eventSource).toBeNull();
  });

  it("renderExistingAssistantMessages renders existing assistant bubbles except those with a loader", () => {
    const bubbleWithDataset = document.createElement("div");
    bubbleWithDataset.className = "chat-bubble-assistant";
    bubbleWithDataset.dataset.markdownContent = "**hello**";

    const bubbleWithText = document.createElement("div");
    bubbleWithText.className = "chat-bubble-assistant";
    bubbleWithText.textContent = "  plain text  ";

    const bubbleWithLoader = document.createElement("div");
    bubbleWithLoader.className = "chat-bubble-assistant";
    bubbleWithLoader.innerHTML = `<span class="chat-loader"></span>`;

    messagesTarget.appendChild(bubbleWithDataset);
    messagesTarget.appendChild(bubbleWithText);
    messagesTarget.appendChild(bubbleWithLoader);

    controller.renderExistingAssistantMessages();

    expect(mockedRenderAssistantBubble).toHaveBeenCalledTimes(2);
    expect(mockedRenderAssistantBubble).toHaveBeenNthCalledWith(
      1,
      bubbleWithDataset,
      "**hello**"
    );
    expect(mockedRenderAssistantBubble).toHaveBeenNthCalledWith(
      2,
      bubbleWithText,
      "plain text"
    );
  });

  it("renderExistingAssistantMessages uses an empty string if markdownContent and textContent are absent", () => {
    const bubbleWithoutContent = document.createElement("div");
    bubbleWithoutContent.className = "chat-bubble-assistant";

    Object.defineProperty(bubbleWithoutContent, "textContent", {
      configurable: true,
      get: () => null,
      set: () => undefined,
    });

    messagesTarget.appendChild(bubbleWithoutContent);

    controller.renderExistingAssistantMessages();

    expect(mockedRenderAssistantBubble).toHaveBeenCalledTimes(1);
    expect(mockedRenderAssistantBubble).toHaveBeenCalledWith(
      bubbleWithoutContent,
      ""
    );
  });

  it("restoreDraftAndSend does nothing if no id is extracted", async () => {
    mockedExtractIdFromIri.mockReturnValue("");
    const sendSpy = jest.spyOn(controller, "send").mockResolvedValue();

    await controller.restoreDraftAndSend();

    expect(mockedExtractIdFromIri).toHaveBeenCalledWith("/logs/123");
    expect(sendSpy).not.toHaveBeenCalled();
  });

  it("restoreDraftAndSend does nothing if no draft and no file draft exist", async () => {
    mockedExtractIdFromIri.mockReturnValue("123");
    const sendSpy = jest.spyOn(controller, "send").mockResolvedValue();

    await controller.restoreDraftAndSend();

    expect(sendSpy).not.toHaveBeenCalled();
  });

  it("restoreDraftAndSend restores the draft, removes it from storage and calls send", async () => {
    mockedExtractIdFromIri.mockReturnValue("123");
    sessionStorage.setItem("chat:draft:123", "draft content");
    const sendSpy = jest.spyOn(controller, "send").mockResolvedValue();

    await controller.restoreDraftAndSend();

    expect(inputTarget.value).toBe("draft content");
    expect(sessionStorage.getItem("chat:draft:123")).toBeNull();
    expect(sendSpy).toHaveBeenCalledTimes(1);

    const eventArg = sendSpy.mock.calls[0][0];
    expect(eventArg).toBeInstanceOf(Event);
    expect(eventArg.type).toBe("submit");
  });

  it("restoreDraftAndSend reconstructs the file from sessionStorage and sets pendingDraftFile", async () => {
    mockedExtractIdFromIri.mockReturnValue("123");
    sessionStorage.setItem("chat:draft:123", "see attached");
    sessionStorage.setItem(
      "chat:draft-file:123",
      JSON.stringify({
        name: "document.pdf",
        type: "application/pdf",
        data: "data:application/pdf;base64,abc123",
      })
    );

    const mockBlob = new Blob(["content"], { type: "application/pdf" });
    global.fetch = jest.fn().mockResolvedValue({
      blob: jest.fn().mockResolvedValue(mockBlob),
    }) as unknown as typeof fetch;

    let capturedFile: File | null = null;
    const sendSpy = jest
      .spyOn(controller, "send")
      .mockImplementation(async () => {
        capturedFile = (controller as any).pendingDraftFile;
      });

    await controller.restoreDraftAndSend();

    expect(sessionStorage.getItem("chat:draft:123")).toBeNull();
    expect(sessionStorage.getItem("chat:draft-file:123")).toBeNull();
    expect(inputTarget.value).toBe("see attached");
    expect(global.fetch).toHaveBeenCalledWith(
      "data:application/pdf;base64,abc123"
    );
    expect(capturedFile).toBeInstanceOf(File);
    expect(capturedFile?.name).toBe("document.pdf");
    expect(capturedFile?.type).toBe("application/pdf");
    expect(sendSpy).toHaveBeenCalledTimes(1);
  });

  it("restoreDraftAndSend works when only a file draft exists (no text)", async () => {
    mockedExtractIdFromIri.mockReturnValue("123");
    sessionStorage.setItem(
      "chat:draft-file:123",
      JSON.stringify({
        name: "image.png",
        type: "image/png",
        data: "data:image/png;base64,xyz",
      })
    );

    const mockBlob = new Blob(["img"], { type: "image/png" });
    global.fetch = jest.fn().mockResolvedValue({
      blob: jest.fn().mockResolvedValue(mockBlob),
    }) as unknown as typeof fetch;

    let capturedFile: File | null = null;
    const sendSpy = jest
      .spyOn(controller, "send")
      .mockImplementation(async () => {
        capturedFile = (controller as any).pendingDraftFile;
      });

    await controller.restoreDraftAndSend();

    expect(inputTarget.value).toBe("");
    expect(capturedFile).toBeInstanceOf(File);
    expect(capturedFile?.name).toBe("image.png");
    expect(sendSpy).toHaveBeenCalledTimes(1);
  });

  it("send returns immediately if both content and file are empty", async () => {
    inputTarget.value = "   ";
    const preventDefault = jest.fn();
    const appendSpy = jest.spyOn(controller, "appendMessage");

    await controller.send({ preventDefault } as unknown as Event);

    expect(preventDefault).toHaveBeenCalledTimes(1);
    expect(appendSpy).not.toHaveBeenCalled();
    expect(axios.post).not.toHaveBeenCalled();
  });

  it("send submits the message and handles an OK response", async () => {
    inputTarget.value = "Bonjour";
    const preventDefault = jest.fn();
    const appendSpy = jest.spyOn(controller, "appendMessage");
    (axios.post as jest.Mock).mockResolvedValue({});

    await controller.send({ preventDefault } as unknown as Event);

    expect(preventDefault).toHaveBeenCalledTimes(1);
    expect(appendSpy).toHaveBeenNthCalledWith(1, "Bonjour", "user", {
      fileName: null,
    });
    expect(appendSpy).toHaveBeenNthCalledWith(2, "", "assistant", {
      pending: true,
    });
    expect(inputTarget.value).toBe("");
    expect(filePreviewTarget.classList.contains("d-none")).toBe(true);
    expect(fileNameTarget.textContent).toBe("");

    const [url, formData] = (axios.post as jest.Mock).mock.calls[0] as [
      string,
      FormData
    ];
    expect(url).toBe("/send");
    expect(formData).toBeInstanceOf(FormData);
    expect(formData.get("input")).toBe("Bonjour");
    expect(formData.get("file")).toBeNull();
  });

  it("send submits a message with a file attached", async () => {
    inputTarget.value = "See attached";
    const mockFile = new File(["content"], "report.pdf", {
      type: "application/pdf",
    });
    Object.defineProperty(controller, "fileTarget", {
      value: { files: [mockFile], value: "" },
      configurable: true,
    });
    const preventDefault = jest.fn();
    const appendSpy = jest.spyOn(controller, "appendMessage");
    (axios.post as jest.Mock).mockResolvedValue({});

    await controller.send({ preventDefault } as unknown as Event);

    expect(appendSpy).toHaveBeenNthCalledWith(1, "See attached", "user", {
      fileName: "report.pdf",
    });

    const [, formData] = (axios.post as jest.Mock).mock.calls[0] as [
      string,
      FormData
    ];
    expect(formData.get("input")).toBe("See attached");
    expect(formData.get("file")).toBe(mockFile);
  });

  it("send proceeds with only a file and no text", async () => {
    inputTarget.value = "";
    const mockFile = new File(["content"], "file.pdf", {
      type: "application/pdf",
    });
    Object.defineProperty(controller, "fileTarget", {
      value: { files: [mockFile], value: "" },
      configurable: true,
    });
    (axios.post as jest.Mock).mockResolvedValue({});
    const appendSpy = jest.spyOn(controller, "appendMessage");

    await controller.send({ preventDefault: jest.fn() } as unknown as Event);

    expect(appendSpy).toHaveBeenNthCalledWith(1, "", "user", {
      fileName: "file.pdf",
    });
    const [, formData] = (axios.post as jest.Mock).mock.calls[0] as [
      string,
      FormData
    ];
    expect(formData.get("file")).toBe(mockFile);
  });

  it("send uses pendingDraftFile and clears it after use", async () => {
    inputTarget.value = "Draft message";
    const mockFile = new File(["content"], "draft.pdf", {
      type: "application/pdf",
    });
    (controller as any).pendingDraftFile = mockFile;
    (axios.post as jest.Mock).mockResolvedValue({});

    await controller.send({ preventDefault: jest.fn() } as unknown as Event);

    expect((controller as any).pendingDraftFile).toBeNull();
    const [, formData] = (axios.post as jest.Mock).mock.calls[0] as [
      string,
      FormData
    ];
    expect(formData.get("file")).toBe(mockFile);
  });

  it("send handles a non-OK HTTP response", async () => {
    inputTarget.value = "Bonjour";
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();
    const failPendingSpy = jest
      .spyOn(controller, "failPending")
      .mockImplementation();
    (axios.post as jest.Mock).mockRejectedValue({ response: { status: 500 } });

    await controller.send({ preventDefault: jest.fn() } as unknown as Event);

    expect(consoleErrorSpy).toHaveBeenCalled();
    expect(failPendingSpy).toHaveBeenCalledWith("Error.");
  });

  it("send handles a network error", async () => {
    inputTarget.value = "Bonjour";
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();
    const failPendingSpy = jest
      .spyOn(controller, "failPending")
      .mockImplementation();
    const networkError = new Error("network");

    (axios.post as jest.Mock).mockRejectedValue(networkError);

    await controller.send({ preventDefault: jest.fn() } as unknown as Event);

    expect(consoleErrorSpy).toHaveBeenCalledWith(networkError);
    expect(failPendingSpy).toHaveBeenCalledWith("Error.");
  });

  it("subscribe does nothing if prerequisites are not met", () => {
    (controller as any).logIriValue = "";
    controller.subscribe();
    expect(mockedEventSourcePolyfill).not.toHaveBeenCalled();

    (controller as any).logIriValue = "/logs/123";
    (controller as any).logTokenValue = "";
    controller.subscribe();
    expect(mockedEventSourcePolyfill).not.toHaveBeenCalled();

    (controller as any).logTokenValue = "token";
    (controller as any).eventSource = { close: jest.fn() };
    controller.subscribe();
    expect(mockedEventSourcePolyfill).not.toHaveBeenCalled();
  });

  it("subscribe creates EventSource and registers listeners", () => {
    controller.subscribe();

    expect(mockedEventSourcePolyfill).toHaveBeenCalledTimes(1);

    const instance = MockEventSourcePolyfill.instances[0];
    expect(instance.url).toBe(
      "https://mercure.example/.well-known/mercure?topic=%2Flogs%2F123"
    );
    expect(instance.options).toEqual({
      headers: {
        Authorization: "Bearer secret-token",
      },
    });

    expect(instance.listeners.text_delta).toBeDefined();
    expect(instance.listeners.assistant_message_complete).toBeDefined();
    expect(instance.listeners.conversation_title_updated).toBeDefined();
  });

  it("subscribe: text_delta removes the loader and appends the chunk to the bubble", () => {
    const scrollSpy = jest
      .spyOn(controller, "scrollToBottom")
      .mockImplementation();

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const pendingBubble = document.createElement("div");
    pendingBubble.innerHTML = `<span class="chat-loader"></span>`;
    (controller as any).pendingAiBubble = pendingBubble;

    instance.emit("text_delta", JSON.stringify({ content: "Hello" }));

    expect(pendingBubble.querySelector(".chat-loader")).toBeNull();
    expect(mockedRenderMarkdown).toHaveBeenCalledWith("Hello");
    expect(pendingBubble.innerHTML).toBe("Hello");
    expect((controller as any).streamingContent).toBe("Hello");
    expect(scrollSpy).toHaveBeenCalled();
  });

  it("subscribe: multiple text_delta events accumulate correctly", () => {
    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;

    instance.emit("text_delta", JSON.stringify({ content: "Hello" }));
    instance.emit("text_delta", JSON.stringify({ content: " world" }));
    instance.emit("text_delta", JSON.stringify({ content: "!" }));

    expect(bubble.innerHTML).toBe("Hello world!");
    expect((controller as any).streamingContent).toBe("Hello world!");
  });

  it("subscribe: text_delta creates a new bubble if pendingAiBubble is null", () => {
    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    expect((controller as any).pendingAiBubble).toBeNull();

    instance.emit("text_delta", JSON.stringify({ content: "Hi" }));

    expect((controller as any).pendingAiBubble).not.toBeNull();
    expect((controller as any).pendingAiBubble.innerHTML).toBe("Hi");
  });

  it("subscribe: text_delta treats missing content field as empty string", () => {
    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;

    instance.emit("text_delta", JSON.stringify({ logIri: "/logs/1" }));

    expect((controller as any).streamingContent).toBe("");
    expect(mockedRenderMarkdown).toHaveBeenCalledWith("");
  });

  it("subscribe: text_delta does not schedule a second rAF while one is already pending", () => {
    let rafCallback: FrameRequestCallback | null = null;
    global.requestAnimationFrame = (cb: FrameRequestCallback): number => {
      rafCallback = cb;
      return 1;
    };

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;

    instance.emit("text_delta", JSON.stringify({ content: "Hello" }));
    // renderPending is now true, rAF not yet fired
    instance.emit("text_delta", JSON.stringify({ content: " world" }));
    // second chunk: renderPending already true, no new rAF scheduled

    expect((controller as any).renderPending).toBe(true);
    expect((controller as any).streamingContent).toBe("Hello world");

    // Flush the single pending rAF — renders the fully accumulated content.
    rafCallback?.(0);
    expect(bubble.innerHTML).toBe("Hello world");
    expect((controller as any).renderPending).toBe(false);
  });

  it("subscribe: rAF callback aborts if renderPending was cleared before it fires", () => {
    let rafCallback: FrameRequestCallback | null = null;
    global.requestAnimationFrame = (cb: FrameRequestCallback): number => {
      rafCallback = cb;
      return 1;
    };

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;

    instance.emit("text_delta", JSON.stringify({ content: "partial" }));
    // rAF queued but not yet fired

    // assistant_message_complete clears renderPending before the frame fires
    (controller as any).renderPending = false;

    mockedRenderMarkdown.mockClear();
    rafCallback?.(0);

    // callback should have bailed out: no renderMarkdown call, bubble unchanged
    expect(mockedRenderMarkdown).not.toHaveBeenCalled();
    expect(bubble.innerHTML).toBe("");
  });

  it("subscribe: text_delta with invalid JSON calls failPending", () => {
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();
    const failPendingSpy = jest
      .spyOn(controller, "failPending")
      .mockImplementation();

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    instance.emit("text_delta", "{invalid json");

    expect(consoleErrorSpy).toHaveBeenCalledWith(
      "Erreur parsing Mercure (text_delta)",
      expect.any(Error)
    );
    expect(failPendingSpy).toHaveBeenCalledWith("Invalid response.");
  });

  it("subscribe: assistant_message_complete renders markdown and resets state", () => {
    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;
    (controller as any).streamingContent = "**bold**";

    const scrollSpy = jest
      .spyOn(controller, "scrollToBottom")
      .mockImplementation();

    instance.emit("assistant_message_complete", "{}");

    expect(mockedRenderAssistantBubble).toHaveBeenCalledWith(
      bubble,
      "**bold**"
    );
    expect((controller as any).pendingAiBubble).toBeNull();
    expect((controller as any).streamingContent).toBe("");
    expect(scrollSpy).toHaveBeenCalled();
  });

  it("subscribe: assistant_message_complete does nothing if there is no pending bubble", () => {
    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    expect((controller as any).pendingAiBubble).toBeNull();

    instance.emit("assistant_message_complete", "{}");

    expect(mockedRenderAssistantBubble).not.toHaveBeenCalled();
  });

  it("subscribe: conversation_title_updated dispatches a custom event", () => {
    const dispatchSpy = jest.spyOn(window, "dispatchEvent");
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    instance.emit(
      "conversation_title_updated",
      JSON.stringify({ logIri: "/logs/999" })
    );
    expect(dispatchSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "chat:conversation-title-updated",
        detail: { logIri: "/logs/999" },
      })
    );

    instance.emit("conversation_title_updated", "");
    expect(dispatchSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "chat:conversation-title-updated",
        detail: { logIri: "/logs/123" },
      })
    );

    instance.emit("conversation_title_updated", "{bad");
    expect(consoleErrorSpy).toHaveBeenCalledWith(
      "Erreur parsing Mercure (title update)",
      expect.any(Error)
    );
  });

  it("subscribe: onerror logs the error", () => {
    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const mercureError = new Event("error");
    instance.onerror?.(mercureError);
    expect(consoleErrorSpy).toHaveBeenCalledWith("Mercure error", mercureError);
  });

  it("appendMessage adds a user message without file", () => {
    const scrollSpy = jest
      .spyOn(controller, "scrollToBottom")
      .mockImplementation();

    const bubble = controller.appendMessage("Salut", "user");

    expect(messagesTarget.children).toHaveLength(1);
    expect(bubble.className).toBe("chat-bubble chat-bubble-user");
    expect(bubble.querySelector(".chat-user-file")).toBeNull();
    expect(bubble.querySelector(".chat-user-text")?.textContent?.trim()).toBe(
      "Salut"
    );
    expect(scrollSpy).toHaveBeenCalledTimes(1);
  });

  it("appendMessage adds a user message with a file chip", () => {
    const bubble = controller.appendMessage("See attached", "user", {
      fileName: "report.pdf",
    });

    expect(bubble.querySelector(".chat-user-file-name")?.textContent).toBe(
      "report.pdf"
    );
    expect(bubble.querySelector(".chat-user-text")?.textContent?.trim()).toBe(
      "See attached"
    );
  });

  it("appendMessage adds a user message with file but no text", () => {
    const bubble = controller.appendMessage("", "user", {
      fileName: "image.png",
    });

    expect(bubble.querySelector(".chat-user-file-name")?.textContent).toBe(
      "image.png"
    );
    expect(bubble.querySelector(".chat-user-text")).toBeNull();
  });

  it("appendMessage adds a pending assistant message", () => {
    const bubble = controller.appendMessage("", "assistant", { pending: true });

    expect(bubble.className).toBe("chat-bubble chat-bubble-assistant");
    expect(bubble.querySelector(".chat-loader")).not.toBeNull();
  });

  it("appendMessage adds a rendered assistant message", () => {
    const bubble = controller.appendMessage("Réponse", "assistant");

    expect(mockedRenderAssistantBubble).toHaveBeenCalledWith(bubble, "Réponse");
  });

  it("failPending does nothing without a pending bubble", () => {
    expect((controller as any).pendingAiBubble).toBeNull();

    controller.failPending("Error.");

    expect((controller as any).streamingContent).toBe("");
  });

  it("failPending marks the bubble as an error and resets streaming state", () => {
    const bubble = document.createElement("div");
    bubble.classList.add("is-pending");
    (controller as any).pendingAiBubble = bubble;
    (controller as any).streamingContent = "partial";

    controller.failPending("Boom");

    expect(bubble.textContent).toBe("Boom");
    expect(bubble.classList.contains("is-pending")).toBe(false);
    expect(bubble.classList.contains("is-error")).toBe(true);
    expect((controller as any).pendingAiBubble).toBeNull();
    expect((controller as any).streamingContent).toBe("");
  });

  it("scrollToBottom sets the scroll to the bottom", () => {
    messagesTarget.scrollTop = 0;

    controller.scrollToBottom();

    expect(messagesTarget.scrollTop).toBe(999);
  });

  it("fileChanged shows the preview when a file is selected", () => {
    const mockFile = new File(["content"], "report.pdf", {
      type: "application/pdf",
    });
    Object.defineProperty(controller, "fileTarget", {
      value: { files: [mockFile] },
      configurable: true,
    });
    filePreviewTarget.classList.add("d-none");

    controller.fileChanged();

    expect(fileNameTarget.textContent).toBe("report.pdf");
    expect(filePreviewTarget.classList.contains("d-none")).toBe(false);
  });

  it("fileChanged hides the preview when no file is selected", () => {
    filePreviewTarget.classList.remove("d-none");
    Object.defineProperty(controller, "fileTarget", {
      value: { files: [] },
      configurable: true,
    });

    controller.fileChanged();

    expect(filePreviewTarget.classList.contains("d-none")).toBe(true);
  });

  it("removeFile clears the file input and hides the preview", () => {
    filePreviewTarget.classList.remove("d-none");
    fileNameTarget.textContent = "file.pdf";

    controller.removeFile();

    expect(filePreviewTarget.classList.contains("d-none")).toBe(true);
    expect(fileNameTarget.textContent).toBe("");
  });

  it("handleKeydown ignores keys other than Enter or Shift+Enter", () => {
    const form = document.createElement("form");
    const requestSubmit = jest.fn();
    form.requestSubmit = requestSubmit;
    form.appendChild(inputTarget);

    const preventDefault = jest.fn();
    controller.handleKeydown({
      key: "Escape",
      shiftKey: false,
      preventDefault,
    } as unknown as KeyboardEvent);

    controller.handleKeydown({
      key: "Enter",
      shiftKey: true,
      preventDefault,
    } as unknown as KeyboardEvent);

    expect(preventDefault).not.toHaveBeenCalled();
    expect(requestSubmit).not.toHaveBeenCalled();
  });

  it("handleKeydown submits the form on Enter without Shift", () => {
    const form = document.createElement("form");
    const requestSubmit = jest.fn();
    form.requestSubmit = requestSubmit;
    form.appendChild(inputTarget);

    const preventDefault = jest.fn();
    controller.handleKeydown({
      key: "Enter",
      shiftKey: false,
      preventDefault,
    } as unknown as KeyboardEvent);

    expect(preventDefault).toHaveBeenCalledTimes(1);
    expect(requestSubmit).toHaveBeenCalledTimes(1);
  });

  it("handleKeydown does not throw if no form is found", () => {
    const preventDefault = jest.fn();

    controller.handleKeydown({
      key: "Enter",
      shiftKey: false,
      preventDefault,
    } as unknown as KeyboardEvent);

    expect(preventDefault).toHaveBeenCalledTimes(1);
  });

  it("revealRateButton removes the d-none class when the target is present", () => {
    expect(rateButtonTarget.classList.contains("d-none")).toBe(true);

    controller.revealRateButton();

    expect(rateButtonTarget.classList.contains("d-none")).toBe(false);
  });

  it("revealRateButton does nothing when the target is absent", () => {
    (controller as any).hasRateButtonTarget = false;

    expect(() => controller.revealRateButton()).not.toThrow();
    expect(rateButtonTarget.classList.contains("d-none")).toBe(true);
  });

  it("connect reveals the rate button when an assistant reply already exists", () => {
    jest
      .spyOn(controller, "renderExistingAssistantMessages")
      .mockImplementation();
    jest.spyOn(controller, "subscribe").mockImplementation();
    jest.spyOn(controller, "restoreDraftAndSend").mockResolvedValue();
    jest.spyOn(controller, "scrollToBottom").mockImplementation();

    const assistantBubble = document.createElement("div");
    assistantBubble.className = "chat-bubble-assistant";
    messagesTarget.appendChild(assistantBubble);

    controller.connect();

    expect(rateButtonTarget.classList.contains("d-none")).toBe(false);
  });

  it("connect keeps the rate button hidden when there is no assistant reply yet", () => {
    jest
      .spyOn(controller, "renderExistingAssistantMessages")
      .mockImplementation();
    jest.spyOn(controller, "subscribe").mockImplementation();
    jest.spyOn(controller, "restoreDraftAndSend").mockResolvedValue();
    jest.spyOn(controller, "scrollToBottom").mockImplementation();

    controller.connect();

    expect(rateButtonTarget.classList.contains("d-none")).toBe(true);
  });

  it("subscribe: assistant_message_complete reveals the rate button", () => {
    jest.spyOn(controller, "scrollToBottom").mockImplementation();

    controller.subscribe();
    const instance = MockEventSourcePolyfill.instances[0];

    const bubble = document.createElement("div");
    (controller as any).pendingAiBubble = bubble;
    (controller as any).streamingContent = "answer";

    instance.emit("assistant_message_complete", "{}");

    expect(rateButtonTarget.classList.contains("d-none")).toBe(false);
  });

  it("submitRating shows an error and does not post when no rating is selected", async () => {
    await controller.submitRating();

    expect(axios.post).not.toHaveBeenCalled();
    expect(ratingErrorTarget.classList.contains("d-none")).toBe(false);
    expect(ratingErrorTarget.textContent).toBe("ai.rating.required");
  });

  it("submitRating posts the rating and comment, hides the modal and removes the button", async () => {
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.name = "rating";
    radio.value = "3";
    radio.checked = true;
    (controller.element as HTMLElement).appendChild(radio);

    ratingCommentTarget.value = "  Great answer  ";

    const modal = document.createElement("div");
    modal.id = "ratingModal";
    document.body.appendChild(modal);
    document.body.appendChild(rateButtonTarget);

    (axios.post as jest.Mock).mockResolvedValue({});

    await controller.submitRating();

    expect(axios.post).toHaveBeenCalledWith("/rate", {
      rating: 3,
      comment: "Great answer",
    });
    expect(mockModalHide).toHaveBeenCalledTimes(1);
    expect(rateButtonTarget.isConnected).toBe(false);

    document.body.removeChild(modal);
  });

  it("submitRating shows an error message when the request fails", async () => {
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.name = "rating";
    radio.value = "2";
    radio.checked = true;
    (controller.element as HTMLElement).appendChild(radio);

    const consoleErrorSpy = jest.spyOn(console, "error").mockImplementation();
    (axios.post as jest.Mock).mockRejectedValue(new Error("422"));

    await controller.submitRating();

    expect(consoleErrorSpy).toHaveBeenCalled();
    expect(ratingErrorTarget.classList.contains("d-none")).toBe(false);
    expect(ratingErrorTarget.textContent).toBe("ai.rating.error");
  });
});
