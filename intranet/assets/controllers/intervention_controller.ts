import { Controller } from "@hotwired/stimulus";

export default class InterventionController extends Controller {
  static targets = [
    "solveTocCheckbox",
    "statusDropdown",
    "symptomsRow",
    "rootCauseRow",
    "solutionRow",
    "endedDateRow",
    "solveTocRow",
  ];

  declare readonly solveTocCheckboxTarget: HTMLInputElement;

  declare readonly statusDropdownTarget: HTMLSelectElement;

  declare readonly symptomsRowTarget: HTMLDivElement;

  declare readonly rootCauseRowTarget: HTMLDivElement;

  declare readonly solutionRowTarget: HTMLDivElement;

  declare readonly endedDateRowTarget: HTMLDivElement;

  declare readonly solveTocRowTarget: HTMLDivElement;

  connect(): void {
    this.onStatusChange();
  }

  static makeFieldVisible(field: HTMLDivElement): void {
    field.classList.remove("d-none");
  }

  static makeFieldInvisible(field: HTMLDivElement): void {
    if (!field.classList.contains("d-none")) {
      field.classList.add("d-none");
    }
  }

  static makeFieldNotRequired(field: HTMLDivElement): void {
    const label = field.querySelector("label");
    if (label) {
      const markerPosition = label.innerHTML.indexOf("*</span>");
      if (markerPosition !== -1) {
        label.innerHTML = label.innerHTML.substring(markerPosition + 8);
      }
    }
    const input = field.querySelector("input");
    if (input) {
      input.required = false;
    }
  }

  static makeFieldRequired(field: HTMLDivElement): void {
    InterventionController.makeFieldNotRequired(field);
    const label = field.querySelector("label");
    if (label) {
      label.innerHTML = `<span style="font-size:80%; color:#ed5565">*</span> ${label.innerHTML}`;
    }
    const input = field.querySelector("input");
    if (input) {
      input.required = true;
    }
  }

  static showRequiredField(field: HTMLDivElement): void {
    InterventionController.makeFieldRequired(field);
    InterventionController.makeFieldVisible(field);
  }

  onTocSolveChange() {
    if (
      this.solveTocCheckboxTarget.checked &&
      this.statusDropdownTarget.value === "SOLVED"
    ) {
      InterventionController.showRequiredField(this.symptomsRowTarget);
      InterventionController.showRequiredField(this.rootCauseRowTarget);
      InterventionController.showRequiredField(this.solutionRowTarget);
    } else {
      InterventionController.makeFieldInvisible(this.symptomsRowTarget);
      InterventionController.makeFieldInvisible(this.rootCauseRowTarget);
      InterventionController.makeFieldInvisible(this.solutionRowTarget);
    }
  }

  onStatusChange() {
    this.onTocSolveChange();
    InterventionController.makeFieldInvisible(this.solveTocRowTarget);
    switch (this.statusDropdownTarget.value) {
      case "TO_CONTINUE":
        InterventionController.makeFieldRequired(this.endedDateRowTarget);
        break;
      case "STARTED":
        InterventionController.makeFieldNotRequired(this.endedDateRowTarget);
        break;
      case "SOLVED":
        InterventionController.makeFieldRequired(this.endedDateRowTarget);
        InterventionController.makeFieldVisible(this.solveTocRowTarget);
        break;
      default:
        break;
    }
  }
}
