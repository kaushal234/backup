import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLDivElement> {
  static targets: string[] = ["collectionContainer"];

  static values = {
    index: Number,
    prototype: String,
  };

  declare prototypeValue: string;

  declare indexValue: number;

  declare collectionContainerTarget: HTMLElement;

  addCollectionElement(): void {
    const item: HTMLDivElement = document.createElement("div");
    item.className = "form-collection-item";
    item.innerHTML = this.prototypeValue.replace(
      /__name__/g,
      String(this.indexValue)
    );
    this.collectionContainerTarget.appendChild(item);
    this.indexValue++;
  }

  removeCollectionElement(event: Event): void {
    const target = event.currentTarget as HTMLElement;
    const item = target.closest(".form-collection-item");

    if (!item) {
      throw new Error(
        `Collection item with class "form-collection-item" not found.`
      );
    }

    if (!this.element.contains(item)) {
      throw new Error(
        `Item "form-collection-item" is not part of the collection.`
      );
    }

    item.remove();
  }
}
