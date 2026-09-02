import axios from "axios";
import { Modal } from "bootstrap";
import Translator from "bazinga-translator";
import { Controller } from "@hotwired/stimulus";
import { EventSourcePolyfill } from "event-source-polyfill";
import {
  extractIdFromIri,
  renderAssistantBubble,
} from "../javascript/chat/chat";
import { renderMarkdown } from "./utils/render_markdown";

type TextDeltaPayload = {
  content?: string;
  logIri?: string;
};

type ConversationTitleUpdatedPayload = {
  logIri?: string;
};

// Stimulus controller for an open conversation (show page).
// Handles sending messages, receiving AI replies via Mercure, and rendering bubbles.
export default class extends Controller<HTMLFormElement> {
  static targets = [
    "messages",
    "input",
    "file",
    "filePreview",
    "fileName",
    "rateButton",
    "ratingComment",
    "ratingError",
  ];

  static values = {
    sendUrl: String,
    rateUrl: String,
    logIri: String,
    logToken: String,
    mercureUrl: String,
  };

  declare readonly messagesTarget: HTMLElement;

  declare readonly inputTarget: HTMLInputElement | HTMLTextAreaElement;

  declare readonly fileTarget: HTMLInputElement;

  declare readonly filePreviewTarget: HTMLElement;

  declare readonly fileNameTarget: HTMLElement;

  declare readonly rateButtonTarget: HTMLButtonElement;

  declare readonly hasRateButtonTarget: boolean;

  declare readonly ratingCommentTarget: HTMLTextAreaElement;

  declare readonly hasRatingCommentTarget: boolean;

  declare readonly ratingErrorTarget: HTMLElement;

  declare readonly hasRatingErrorTarget: boolean;

  declare readonly sendUrlValue: string;

  declare readonly rateUrlValue: string;

  declare readonly logIriValue: string;

  declare readonly logTokenValue: string;

  declare readonly mercureUrlValue: string;

  private eventSource: EventSourcePolyfill | null = null;

  private pendingAiBubble: HTMLDivElement | null = null;

  private streamingContent = "";

  private renderPending = false;

  // File passed from the home page draft (cannot be set on <input type="file"> programmatically).
  private pendingDraftFile: File | null = null;

  connect(): void {
    this.renderExistingAssistantMessages();
    // Show the rating button right away if the conversation already has a reply.
    if (this.messagesTarget.querySelector(".chat-bubble-assistant")) {
      this.revealRateButton();
    }
    this.subscribe();
    // If the user came from the home page with a draft, send it immediately.
    this.restoreDraftAndSend().catch((error: unknown) => {
      console.error("Failed to restore draft", error);
    });
    this.scrollToBottom();
  }

  disconnect(): void {
    this.eventSource?.close();
    this.eventSource = null;
  }

  // Parse markdown in assistant bubbles that were server-rendered (page load / history).
  renderExistingAssistantMessages(): void {
    this.messagesTarget
      .querySelectorAll<HTMLElement>(".chat-bubble-assistant")
      .forEach((bubble) => {
        if (bubble.querySelector(".chat-loader")) return;

        const rawContent =
          bubble.dataset.markdownContent ?? bubble.textContent?.trim() ?? "";

        renderAssistantBubble(bubble, rawContent);
      });
  }

  // Picks up text + file stored in sessionStorage by chat_home_controller after redirect.
  async restoreDraftAndSend(): Promise<void> {
    const id = extractIdFromIri(this.logIriValue);
    if (!id) return;

    const draftKey = `chat:draft:${id}`;
    const draftFileKey = `chat:draft-file:${id}`;
    const draft = sessionStorage.getItem(draftKey);
    const draftFileJson = sessionStorage.getItem(draftFileKey);

    if (!draft && !draftFileJson) return;

    sessionStorage.removeItem(draftKey);
    sessionStorage.removeItem(draftFileKey);

    this.inputTarget.value = draft ?? "";

    // Reconstruct the File from its base64 data URL so send() can attach it.
    if (draftFileJson) {
      const { name, type, data } = JSON.parse(draftFileJson) as {
        name: string;
        type: string;
        data: string;
      };
      const blob = await fetch(data).then((r) => r.blob());
      this.pendingDraftFile = new File([blob], name, { type });
    }

    const submitEvent = new Event("submit", {
      bubbles: true,
      cancelable: true,
    });

    await this.send(submitEvent);
  }

  // Called on form submit: posts text + optional file to the API as multipart/form-data.
  async send(event: Event): Promise<void> {
    event.preventDefault();

    const content = this.inputTarget.value.trim();
    // pendingDraftFile takes priority when the message was initiated from the home page.
    const file = this.pendingDraftFile ?? this.fileTarget.files?.[0] ?? null;
    this.pendingDraftFile = null;

    if (!content && !file) return;

    const fileName = file?.name ?? null;

    this.appendMessage(content, "user", { fileName });

    this.inputTarget.value = "";
    this.fileTarget.value = "";
    this.filePreviewTarget.classList.add("d-none");
    this.fileNameTarget.textContent = "";

    this.pendingAiBubble = this.appendMessage("", "assistant", {
      pending: true,
    });

    try {
      const formData = new FormData();
      formData.append("input", content);

      if (file) {
        formData.append("file", file);
      }

      await axios.post(this.sendUrlValue, formData);
    } catch (error) {
      console.error(error);
      this.failPending("Error.");
    }
  }

  // Opens a Mercure SSE connection to receive the AI reply in real time.
  subscribe(): void {
    if (!this.logIriValue || !this.logTokenValue || this.eventSource) return;

    const url = new URL(this.mercureUrlValue);
    url.searchParams.append("topic", this.logIriValue);

    this.eventSource = new EventSourcePolyfill(url.toString(), {
      headers: {
        Authorization: `Bearer ${this.logTokenValue}`,
      },
    });

    // AI reply chunk: append to the bubble as it arrives.
    this.eventSource.addEventListener("text_delta", (event: MessageEvent) => {
      try {
        const payload = JSON.parse(String(event.data)) as TextDeltaPayload;
        const chunk = payload.content ?? "";

        if (!this.pendingAiBubble) {
          this.pendingAiBubble = this.appendMessage("", "assistant");
        }

        const bubble = this.pendingAiBubble;
        if (bubble.querySelector(".chat-loader")) {
          bubble.innerHTML = "";
        }

        this.streamingContent += chunk;

        if (!this.renderPending) {
          this.renderPending = true;
          requestAnimationFrame(() => {
            if (!this.renderPending) return;
            this.renderPending = false;
            bubble.innerHTML = renderMarkdown(this.streamingContent);
            this.scrollToBottom();
          });
        }
      } catch (error) {
        console.error("Erreur parsing Mercure (text_delta)", error);
        this.failPending("Invalid response.");
      }
    });

    // End of stream: cancel any pending frame then do the final render (adds copy buttons).
    this.eventSource.addEventListener("assistant_message_complete", () => {
      if (!this.pendingAiBubble) return;

      this.renderPending = false;

      renderAssistantBubble(this.pendingAiBubble, this.streamingContent);
      this.pendingAiBubble = null;
      this.streamingContent = "";
      this.scrollToBottom();

      // First reply received: let the user rate the conversation.
      this.revealRateButton();
    });

    // Title update: bubble up to the sidebar so it can refresh the conversation name.
    this.eventSource.addEventListener(
      "conversation_title_updated",
      (event: MessageEvent) => {
        try {
          const payload = JSON.parse(
            String(event.data || "{}")
          ) as ConversationTitleUpdatedPayload;

          window.dispatchEvent(
            new CustomEvent("chat:conversation-title-updated", {
              detail: {
                logIri: payload.logIri ?? this.logIriValue,
              },
            })
          );
        } catch (error) {
          console.error("Erreur parsing Mercure (title update)", error);
        }
      }
    );

    this.eventSource.onerror = (error: Event): void => {
      console.error("Mercure error", error);
    };
  }

  // Creates a message row + bubble and appends it to the thread.
  appendMessage(
    content: string,
    type: "user" | "assistant",
    {
      pending = false,
      fileName = null,
    }: { pending?: boolean; fileName?: string | null } = {}
  ): HTMLDivElement {
    const row = document.createElement("div");
    row.className =
      type === "user"
        ? "d-flex justify-content-end mb-3"
        : "d-flex justify-content-start mb-3";

    const bubble = document.createElement("div");
    bubble.className =
      type === "user"
        ? "chat-bubble chat-bubble-user"
        : "chat-bubble chat-bubble-assistant";

    if (pending) {
      bubble.innerHTML = `
        <span class="chat-loader">
          <span></span>
          <span></span>
          <span></span>
        </span>
      `;
    } else if (type === "assistant") {
      renderAssistantBubble(bubble, content);
    } else {
      if (fileName) {
        const fileDiv = document.createElement("div");
        fileDiv.className = "chat-user-file";
        const icon = document.createElement("i");
        icon.className = "fa fa-file";
        const nameSpan = document.createElement("span");
        nameSpan.className = "chat-user-file-name";
        nameSpan.textContent = fileName;
        fileDiv.append(icon, nameSpan);
        bubble.appendChild(fileDiv);
      }
      if (content) {
        const textDiv = document.createElement("div");
        textDiv.className = "chat-user-text";
        textDiv.textContent = content;
        bubble.appendChild(textDiv);
      }
    }

    row.appendChild(bubble);
    const container =
      this.messagesTarget.querySelector<HTMLElement>(".chat-container") ??
      this.messagesTarget;
    container.appendChild(row);
    this.scrollToBottom();

    return bubble;
  }

  failPending(message: string): void {
    if (!this.pendingAiBubble) return;

    this.pendingAiBubble.textContent = message;
    this.pendingAiBubble.classList.remove("is-pending");
    this.pendingAiBubble.classList.add("is-error");
    this.pendingAiBubble = null;
    this.streamingContent = "";
  }

  scrollToBottom(): void {
    this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
  }

  // Reveals the "give feedback" button. Absent from the DOM once the conversation is rated.
  revealRateButton(): void {
    if (!this.hasRateButtonTarget) return;

    this.rateButtonTarget.classList.remove("d-none");
  }

  // Posts the rating (1-4) + optional comment for the whole conversation.
  // A conversation can only be rated once, so the button is removed on success.
  async submitRating(): Promise<void> {
    const selected = this.element.querySelector<HTMLInputElement>(
      'input[name="rating"]:checked'
    );

    if (this.hasRatingErrorTarget) {
      this.ratingErrorTarget.classList.add("d-none");
    }

    if (!selected) {
      this.showRatingError(Translator.trans("ai.rating.required", {}, "ai"));
      return;
    }

    try {
      await axios.post(this.rateUrlValue, {
        rating: Number(selected.value),
        comment: this.hasRatingCommentTarget
          ? this.ratingCommentTarget.value.trim()
          : "",
      });

      const modal = document.getElementById("ratingModal");
      if (modal) {
        Modal.getOrCreateInstance(modal).hide();
      }

      // The rating is final: drop the button so it cannot be reopened.
      if (this.hasRateButtonTarget) {
        this.rateButtonTarget.remove();
      }
    } catch (error) {
      console.error(error);
      this.showRatingError(Translator.trans("ai.rating.error", {}, "ai"));
    }
  }

  showRatingError(message: string): void {
    if (!this.hasRatingErrorTarget) return;

    this.ratingErrorTarget.textContent = message;
    this.ratingErrorTarget.classList.remove("d-none");
  }

  // Updates the file preview chip when the user selects a file.
  fileChanged(): void {
    const file = this.fileTarget.files?.[0];

    if (file) {
      this.fileNameTarget.textContent = file.name;
      this.filePreviewTarget.classList.remove("d-none");
    } else {
      this.filePreviewTarget.classList.add("d-none");
    }
  }

  removeFile(): void {
    this.fileTarget.value = "";
    this.filePreviewTarget.classList.add("d-none");
    this.fileNameTarget.textContent = "";
  }

  handleKeydown(event: KeyboardEvent): void {
    if (event.key !== "Enter" || event.shiftKey) return;

    event.preventDefault();

    const form = this.inputTarget.closest("form");
    form?.requestSubmit();
  }
}
