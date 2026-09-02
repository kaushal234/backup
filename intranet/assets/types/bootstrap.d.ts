declare module "bootstrap" {
  // Minimal typing for the Bootstrap 5 Modal API we use from Stimulus controllers.
  // Bootstrap's JS is loaded globally (see assets/index.js), this only types the
  // pieces consumed in TypeScript.
  export class Modal {
    static getOrCreateInstance(element: Element): Modal;

    hide(): void;
    show(): void;
  }

  export class Toast {
    static getOrCreateInstance(element: Element): Toast;
    show(): void;
  }

}