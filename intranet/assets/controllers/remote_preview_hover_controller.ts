import { Controller } from "@hotwired/stimulus";
import { createPopper, Instance as PopperInstance } from "@popperjs/core";

class RemotePreviewHoverController extends Controller {
  static targets = ["trigger", "box"];

  declare readonly triggerTarget: HTMLElement;
  declare readonly boxTarget: HTMLElement;

  private popper: PopperInstance | null = null;
  private resizeObserver: ResizeObserver | null = null;

  // Stimulus 3.1 (current version on intranet) doesn't support the `.esc` keyboard event
  // filter added in later versions, so Escape is handled with a plain
  // document listener instead of a `keydown.esc@document->...` action.
  private readonly handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape") {
      this.close();
    }
  };

  private readonly handleOutsideClick = (event: MouseEvent): void => {
    if (!this.element.contains(event.target as Node)) {
      this.close();
    }
  };

  private static current: RemotePreviewHoverController | null = null;

  connect(): void {
    document.addEventListener("keydown", this.handleKeydown);
  }

  disconnect(): void {
    document.removeEventListener("keydown", this.handleKeydown);
    document.removeEventListener("click", this.handleOutsideClick);

    if (RemotePreviewHoverController.current === this) {
      RemotePreviewHoverController.current = null;
    }

    this.destroyPopper();
  }

  open(): void {
    if (
      RemotePreviewHoverController.current &&
      RemotePreviewHoverController.current !== this
    ) {
      RemotePreviewHoverController.current.close();
    }
    RemotePreviewHoverController.current = this;

    this.boxTarget.classList.add("show");

    this.destroyPopper();
    this.popper = createPopper(this.triggerTarget, this.boxTarget, {
      strategy: "fixed",
      placement: "left",
      modifiers: [{ name: "offset", options: { offset: [0, 8] } }],
    });

    // The box grows once the preview data replaces the loading spinner.
    // keep it correctly positioned when that happens.
    this.resizeObserver = new ResizeObserver(() => this.popper?.update());
    this.resizeObserver.observe(this.boxTarget);

    document.removeEventListener("click", this.handleOutsideClick);
    document.addEventListener("click", this.handleOutsideClick);
  }

  close(): void {
    this.boxTarget.classList.remove("show");
    document.removeEventListener("click", this.handleOutsideClick);

    if (RemotePreviewHoverController.current === this) {
      RemotePreviewHoverController.current = null;
    }

    this.destroyPopper();
  }

  private destroyPopper(): void {
    this.resizeObserver?.disconnect();
    this.resizeObserver = null;
    this.popper?.destroy();
    this.popper = null;
  }
}

export default RemotePreviewHoverController;
