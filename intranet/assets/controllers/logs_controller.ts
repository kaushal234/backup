import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLElement> {
  connect(): void {
    this.mount();

    document.addEventListener("turbo:frame-load", this.onFrameLoad);
  }

  disconnect(): void {
    document.removeEventListener("turbo:frame-load", this.onFrameLoad);
  }

  private onFrameLoad = (event: Event): void => {
    if (event.target === this.element) {
      this.mount();
    }
  };

  private mount(): void {
    if (typeof window.mountLogsBlocks !== "function") {
      return;
    }

    window.mountLogsBlocks(this.element);
  }
}
