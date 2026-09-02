import { Controller } from "@hotwired/stimulus";

interface PlanItemType {
  "@id": string;
  requestablePriorDelivery: boolean;
  requestableAtPurchaseOrder: boolean;
}

export default class extends Controller<HTMLElement> {
  static targets: string[] = ["type", "prior", "purchase"];

  static values = {
    types: Array,
  };

  declare typesValue: PlanItemType[];
  declare typeTarget: HTMLSelectElement;
  declare priorTarget: HTMLInputElement;
  declare purchaseTarget: HTMLInputElement;

  connect(): void {
    this.updateCheckboxes();
  }

  updateCheckboxes(): void {
    const selectedType = this.typesValue.find(
      (type) => type["@id"] === this.typeTarget.value
    );

    this.setCheckboxState(
      this.priorTarget,
      selectedType?.requestablePriorDelivery ?? false
    );
    this.setCheckboxState(
      this.purchaseTarget,
      selectedType?.requestableAtPurchaseOrder ?? false
    );
  }

  private setCheckboxState(target: HTMLInputElement, allowed: boolean): void {
    const checkbox = target;
    checkbox.disabled = !allowed;

    if (!allowed) {
      checkbox.checked = false;
    }
  }
}
