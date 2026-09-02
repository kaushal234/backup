import { describe, expect, test, beforeEach, afterEach } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import InterventionController from "../controllers/intervention_controller";

describe("InterventionController", () => {
  let application: Application;
  let element: HTMLDivElement;
  let controller: InterventionController;
  let solveTocCheckbox: HTMLInputElement;
  let statusDropdown: HTMLSelectElement;
  let symptomsRow: HTMLDivElement;
  let rootCauseRow: HTMLDivElement;
  let solutionRow: HTMLDivElement;
  let endedDateRow: HTMLDivElement;
  let solveTocRow: HTMLDivElement;

  beforeEach(async () => {
    document.body.innerHTML = `
      <div data-controller="intervention">
        <input 
          type="checkbox" 
          id="solveToc" 
          data-intervention-target="solveTocCheckbox"
          data-action="intervention#onTocSolveChange"
        />
        <select 
          id="status" 
          data-intervention-target="statusDropdown"
          data-action="intervention#onStatusChange"
        >
          <option value="TO_CONTINUE">To Continue</option>
          <option value="STARTED">Started</option>
          <option value="SOLVED">Solved</option>
        </select>
        <div data-intervention-target="symptomsRow" class="d-none">
          <label>Symptoms <span style="font-size:80%; color:#ed5565">*</span></label>
          <input type="text" required />
        </div>
        <div data-intervention-target="rootCauseRow" class="d-none">
          <label>Root Cause <span style="font-size:80%; color:#ed5565">*</span></label>
          <input type="text" required />
        </div>
        <div data-intervention-target="solutionRow" class="d-none">
          <label>Solution <span style="font-size:80%; color:#ed5565">*</span></label>
          <input type="text" required />
        </div>
        <div data-intervention-target="endedDateRow" class="form-group">
          <label>Ended Date</label>
          <input type="date" />
        </div>
        <div data-intervention-target="solveTocRow" class="d-none">
          <label>Solve TOC</label>
          <input type="checkbox" />
        </div>
      </div>
    `;

    element = document.querySelector(
      "[data-controller='intervention']"
    ) as HTMLDivElement;
    solveTocCheckbox = document.querySelector("#solveToc") as HTMLInputElement;
    statusDropdown = document.querySelector("#status") as HTMLSelectElement;
    symptomsRow = document.querySelector(
      "[data-intervention-target='symptomsRow']"
    ) as HTMLDivElement;
    rootCauseRow = document.querySelector(
      "[data-intervention-target='rootCauseRow']"
    ) as HTMLDivElement;
    solutionRow = document.querySelector(
      "[data-intervention-target='solutionRow']"
    ) as HTMLDivElement;
    endedDateRow = document.querySelector(
      "[data-intervention-target='endedDateRow']"
    ) as HTMLDivElement;
    solveTocRow = document.querySelector(
      "[data-intervention-target='solveTocRow']"
    ) as HTMLDivElement;

    application = Application.start();
    application.register("intervention", InterventionController);
    await Promise.resolve();

    controller = application.getControllerForElementAndIdentifier(
      element,
      "intervention"
    ) as InterventionController;
  });

  afterEach(() => {
    application.stop();
  });

  describe("connect", () => {
    test("should initialize and call onStatusChange on connect", () => {
      expect(controller).toBeDefined();
      // Check that onStatusChange was called during connect
      expect(endedDateRow.classList.contains("d-none")).toBe(false);
    });
  });

  describe("makeFieldVisible", () => {
    test("should remove d-none class from field", () => {
      const field = document.createElement("div");
      field.classList.add("d-none");

      InterventionController.makeFieldVisible(field);

      expect(field.classList.contains("d-none")).toBe(false);
    });

    test("should work on field without d-none class", () => {
      const field = document.createElement("div");

      InterventionController.makeFieldVisible(field);

      expect(field.classList.contains("d-none")).toBe(false);
    });
  });

  describe("makeFieldInvisible", () => {
    test("should add d-none class to field", () => {
      const field = document.createElement("div");

      InterventionController.makeFieldInvisible(field);

      expect(field.classList.contains("d-none")).toBe(true);
    });

    test("should not add d-none class if already present", () => {
      const field = document.createElement("div");
      field.classList.add("d-none");

      InterventionController.makeFieldInvisible(field);

      expect(field.classList.contains("d-none")).toBe(true);
    });
  });

  describe("makeFieldNotRequired", () => {
    test("should remove required marker from label", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = `<span style="font-size:80%; color:#ed5565">*</span> Test Field`;
      field.appendChild(label);

      const input = document.createElement("input");
      input.required = true;
      field.appendChild(input);

      InterventionController.makeFieldNotRequired(field);

      expect(label.innerHTML).toBe(" Test Field");
      expect(input.required).toBe(false);
    });

    test("should handle field without label", () => {
      const field = document.createElement("div");
      const input = document.createElement("input");
      input.required = true;
      field.appendChild(input);

      InterventionController.makeFieldNotRequired(field);

      expect(input.required).toBe(false);
    });

    test("should handle field without input", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = `<span style="font-size:80%; color:#ed5565">*</span> Test Field`;
      field.appendChild(label);

      InterventionController.makeFieldNotRequired(field);

      expect(label.innerHTML).toBe(" Test Field");
    });

    test("should handle label with no marker", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = "Test Field";
      field.appendChild(label);

      InterventionController.makeFieldNotRequired(field);

      expect(label.innerHTML).toBe("Test Field");
    });
  });

  describe("makeFieldRequired", () => {
    test("should add required marker to label and set required on input", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = "Test Field";
      field.appendChild(label);

      const input = document.createElement("input");
      input.required = false;
      field.appendChild(input);

      InterventionController.makeFieldRequired(field);

      expect(label.innerHTML).toContain(
        '<span style="font-size:80%; color:#ed5565">*</span>'
      );
      expect(label.innerHTML).toContain("Test Field");
      expect(input.required).toBe(true);
    });

    test("should remove old marker before adding new one", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = `<span style="font-size:80%; color:#ed5565">*</span> Test Field`;
      field.appendChild(label);

      const input = document.createElement("input");
      field.appendChild(input);

      InterventionController.makeFieldRequired(field);

      const markerCount = (label.innerHTML.match(/\*<\/span>/g) || []).length;
      expect(markerCount).toBe(1);
      expect(input.required).toBe(true);
    });

    test("should handle field without label", () => {
      const field = document.createElement("div");
      const input = document.createElement("input");
      field.appendChild(input);

      InterventionController.makeFieldRequired(field);

      expect(input.required).toBe(true);
    });

    test("should handle field without input", () => {
      const field = document.createElement("div");
      const label = document.createElement("label");
      label.innerHTML = "Test Field";
      field.appendChild(label);

      InterventionController.makeFieldRequired(field);

      expect(label.innerHTML).toContain(
        '<span style="font-size:80%; color:#ed5565">*</span>'
      );
    });
  });

  describe("showRequiredField", () => {
    test("should make field visible and required", () => {
      const field = document.createElement("div");
      field.classList.add("d-none");
      const label = document.createElement("label");
      label.innerHTML = "Test Field";
      field.appendChild(label);
      const input = document.createElement("input");
      field.appendChild(input);

      InterventionController.showRequiredField(field);

      expect(field.classList.contains("d-none")).toBe(false);
      expect(label.innerHTML).toContain(
        '<span style="font-size:80%; color:#ed5565">*</span>'
      );
      expect(input.required).toBe(true);
    });
  });

  describe("onTocSolveChange", () => {
    test("should show symptoms, root cause, and solution when checkbox is checked and status is SOLVED", () => {
      statusDropdown.value = "SOLVED";
      solveTocCheckbox.checked = true;

      controller.onTocSolveChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(false);
      expect(rootCauseRow.classList.contains("d-none")).toBe(false);
      expect(solutionRow.classList.contains("d-none")).toBe(false);

      const symptomsInput = symptomsRow.querySelector(
        "input"
      ) as HTMLInputElement;
      const rootCauseInput = rootCauseRow.querySelector(
        "input"
      ) as HTMLInputElement;
      const solutionInput = solutionRow.querySelector(
        "input"
      ) as HTMLInputElement;

      expect(symptomsInput.required).toBe(true);
      expect(rootCauseInput.required).toBe(true);
      expect(solutionInput.required).toBe(true);
    });

    test("should hide fields when checkbox is unchecked", () => {
      statusDropdown.value = "SOLVED";
      solveTocCheckbox.checked = false;

      controller.onTocSolveChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(true);
      expect(rootCauseRow.classList.contains("d-none")).toBe(true);
      expect(solutionRow.classList.contains("d-none")).toBe(true);
    });

    test("should hide fields when status is not SOLVED", () => {
      statusDropdown.value = "STARTED";
      solveTocCheckbox.checked = true;

      controller.onTocSolveChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(true);
      expect(rootCauseRow.classList.contains("d-none")).toBe(true);
      expect(solutionRow.classList.contains("d-none")).toBe(true);
    });

    test("should hide fields when both checkbox is unchecked and status is not SOLVED", () => {
      statusDropdown.value = "STARTED";
      solveTocCheckbox.checked = false;

      controller.onTocSolveChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(true);
      expect(rootCauseRow.classList.contains("d-none")).toBe(true);
      expect(solutionRow.classList.contains("d-none")).toBe(true);
    });
  });

  describe("onStatusChange", () => {
    test("should make endedDateRow required when status is TO_CONTINUE", () => {
      statusDropdown.value = "TO_CONTINUE";

      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(true);
      const endedDateInput = endedDateRow.querySelector(
        "input"
      ) as HTMLInputElement;
      expect(endedDateInput.required).toBe(true);
    });

    test("should make endedDateRow not required when status is STARTED", () => {
      statusDropdown.value = "STARTED";

      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(true);
      const endedDateInput = endedDateRow.querySelector(
        "input"
      ) as HTMLInputElement;
      expect(endedDateInput.required).toBe(false);
    });

    test("should make endedDateRow required and show solveTocRow when status is SOLVED", () => {
      statusDropdown.value = "SOLVED";

      controller.onStatusChange();

      const endedDateInput = endedDateRow.querySelector(
        "input"
      ) as HTMLInputElement;
      expect(endedDateInput.required).toBe(true);
      expect(solveTocRow.classList.contains("d-none")).toBe(false);
    });

    test("should hide solveTocRow by default at start of onStatusChange", () => {
      statusDropdown.value = "TO_CONTINUE";

      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(true);
    });

    test("should call onTocSolveChange during onStatusChange", () => {
      statusDropdown.value = "SOLVED";
      solveTocCheckbox.checked = true;

      controller.onStatusChange();

      // If fields are visible, it means onTocSolveChange was called
      expect(symptomsRow.classList.contains("d-none")).toBe(false);
      expect(rootCauseRow.classList.contains("d-none")).toBe(false);
      expect(solutionRow.classList.contains("d-none")).toBe(false);
    });

    test("should handle default case for unknown status", () => {
      statusDropdown.value = "UNKNOWN_STATUS";

      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(true);
    });

    test("should work with TO_CONTINUE status and hide solve fields", () => {
      statusDropdown.value = "TO_CONTINUE";
      solveTocCheckbox.checked = true;

      controller.onStatusChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(true);
      expect(rootCauseRow.classList.contains("d-none")).toBe(true);
      expect(solutionRow.classList.contains("d-none")).toBe(true);
    });

    test("should work with STARTED status and hide solve fields", () => {
      statusDropdown.value = "STARTED";
      solveTocCheckbox.checked = true;

      controller.onStatusChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(true);
      expect(rootCauseRow.classList.contains("d-none")).toBe(true);
      expect(solutionRow.classList.contains("d-none")).toBe(true);
    });
  });

  describe("integration scenarios", () => {
    test("should handle full workflow: start -> solved with TOC", () => {
      // Initial state
      expect(solveTocRow.classList.contains("d-none")).toBe(true);

      // Change to SOLVED
      statusDropdown.value = "SOLVED";
      controller.onStatusChange();
      expect(solveTocRow.classList.contains("d-none")).toBe(false);

      // Check the solve TOC checkbox
      solveTocCheckbox.checked = true;
      controller.onTocSolveChange();

      expect(symptomsRow.classList.contains("d-none")).toBe(false);
      expect(rootCauseRow.classList.contains("d-none")).toBe(false);
      expect(solutionRow.classList.contains("d-none")).toBe(false);
    });

    test("should handle workflow: solved -> started", () => {
      // Set to SOLVED first
      statusDropdown.value = "SOLVED";
      solveTocCheckbox.checked = true;
      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(false);
      expect(symptomsRow.classList.contains("d-none")).toBe(false);

      // Change to STARTED
      statusDropdown.value = "STARTED";
      controller.onStatusChange();

      expect(solveTocRow.classList.contains("d-none")).toBe(true);
      expect(symptomsRow.classList.contains("d-none")).toBe(true);
    });

    test("should handle toggling solve TOC checkbox in SOLVED state", () => {
      statusDropdown.value = "SOLVED";
      controller.onStatusChange();

      // Check the checkbox
      solveTocCheckbox.checked = true;
      controller.onTocSolveChange();
      expect(symptomsRow.classList.contains("d-none")).toBe(false);

      // Uncheck the checkbox
      solveTocCheckbox.checked = false;
      controller.onTocSolveChange();
      expect(symptomsRow.classList.contains("d-none")).toBe(true);
    });
  });
});
