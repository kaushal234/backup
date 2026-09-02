import { Controller } from "@hotwired/stimulus";
import { TempusDominus, Options } from "@eonasdan/tempus-dominus";

export default class extends Controller<HTMLFormElement> {
  private datepicker: TempusDominus | undefined;

  connect(): void {
    this.init();
  }

  init(): void {
    let options: Options | undefined;
    const dataOptions: string | undefined =
      this.element.dataset.options || undefined;

    if (dataOptions !== undefined) {
      options = JSON.parse(dataOptions);
    }

    this.datepicker = new TempusDominus(this.element, options);

    (this.element as any).tempusDominusInstance = this.datepicker;
  }

  disconnect() {
    if (this.datepicker) {
      this.datepicker.dispose();
    }
  }
}
