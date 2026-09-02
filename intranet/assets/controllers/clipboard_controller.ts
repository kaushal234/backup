import Clipboard from "@stimulus-components/clipboard";
import { Toast } from "bootstrap";

export default class extends Clipboard {
  copied(): void {
    super.copied();
    const el = this.element.querySelector("[data-clipboard-feedback]");
    if (el) {
      Toast.getOrCreateInstance(el).show();
    }
  }
}
