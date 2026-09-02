import { Controller } from "@hotwired/stimulus";

function runHinclude(): void {
  if (typeof hinclude !== "undefined" && typeof hinclude?.run === "function") {
    hinclude.run();
  }
}

export default class extends Controller<HTMLElement> {
  connect(): void {
    runHinclude();

    document.addEventListener("turbo:frame-load", this.onFrameLoad);
  }

  disconnect(): void {
    document.removeEventListener("turbo:frame-load", this.onFrameLoad);
  }

  private onFrameLoad = (event: Event): void => {
    if (event.target === this.element) {
      runHinclude();
    }
  };
}
