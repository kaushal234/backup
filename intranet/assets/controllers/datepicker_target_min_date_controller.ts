import { Controller } from "@hotwired/stimulus";
import { TempusDominus } from "@eonasdan/tempus-dominus";

interface TempusChangeEvent {
  date: Date | null;
}

export default class extends Controller<HTMLFormElement> {
  static values = {
    target: String,
  };

  declare targetValue: string;

  connect(): void {
    this.element.addEventListener("change.td", (e: Event) => {
      const event = e as CustomEvent<TempusChangeEvent>;
      const target = document.getElementById(this.targetValue);
      const targetDatePicker = (target as any)
        .tempusDominusInstance as TempusDominus;
      targetDatePicker.updateOptions({
        restrictions: { minDate: event.detail.date },
      });
    });
  }
}
