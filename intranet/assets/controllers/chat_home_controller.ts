import axios from "axios";
import { Controller } from "@hotwired/stimulus";
import {
  extractIdFromIri,
  redirectToConversation,
} from "../javascript/chat/chat";

// Converts a File to a base64 data URL so it can be stored in sessionStorage.
function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

// Stimulus controller for the chat home page (new conversation form).
// Creates the conversation, serialises the draft + file to sessionStorage, then redirects.
// chat_controller picks up the draft on the show page and sends it automatically.
export default class ChatHomeController extends Controller {
  static targets = ["input", "file", "filePreview", "fileName"];

  static values = {
    createUrl: String,
  };

  declare readonly inputTarget: HTMLInputElement | HTMLTextAreaElement;

  declare readonly fileTarget: HTMLInputElement;

  declare readonly filePreviewTarget: HTMLElement;

  declare readonly fileNameTarget: HTMLElement;

  declare readonly createUrlValue: string;

  autoResize(): void {
    const textarea = this.inputTarget as HTMLTextAreaElement;
    textarea.style.height = "auto";
    textarea.style.height = `${textarea.scrollHeight}px`;
  }

  async startConversation(event: Event): Promise<void> {
    event.preventDefault();

    const content = this.inputTarget.value.trim();
    const file = this.fileTarget.files?.[0] ?? null;

    if (!content && !file) return;

    // Create an empty conversation first to get its IRI / numeric id.
    let conversation: { logIri?: string };
    try {
      const { data } = await axios.post<{ logIri?: string }>(
        this.createUrlValue
      );
      conversation = data;
    } catch {
      console.error("Failed to create conversation");
      return;
    }
    const { logIri } = conversation;

    if (!logIri) {
      console.error("Missing logIri in response", conversation);
      return;
    }

    const id = extractIdFromIri(logIri);

    if (!id) {
      console.error("Failed to extract id from IRI", logIri);
      return;
    }

    // Reset textarea height before redirect.
    const textarea = this.inputTarget as HTMLTextAreaElement;
    textarea.style.height = "auto";

    // Persist draft so chat_controller can send it after the redirect.
    sessionStorage.setItem(`chat:draft:${id}`, content);

    // Files cannot survive a page navigation — serialise to base64 in sessionStorage.
    if (file) {
      const base64 = await fileToBase64(file);
      sessionStorage.setItem(
        `chat:draft-file:${id}`,
        JSON.stringify({ name: file.name, type: file.type, data: base64 })
      );
    }

    redirectToConversation(id);
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
  }

  handleKeydown(event: KeyboardEvent): void {
    if (event.key === "Enter" && !event.shiftKey) {
      event.preventDefault();
      this.startConversation(event).catch((error: unknown) => {
        console.error("Failed to start conversation", error);
      });
    }
  }
}
