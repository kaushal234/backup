import { Application, Controller } from "@hotwired/stimulus";
import AutocompleteController from "../../controllers/autocomplete_controller";
import AutofillProjectManagerController from "../../controllers/mis/project/autofill_project_manager_controller";
import CascadingSelectController from "../../controllers/cascading_select_controller";

const controllers = {
  "cascading-select": CascadingSelectController,
  "mis--project--autofill-project-manager": AutofillProjectManagerController,
  autocomplete: AutocompleteController,
  "symfony--ux-autocomplete--autocomplete": class extends Controller {},
};

export function startStimulus() {
  const application = Application.start();

  Object.entries(controllers).forEach(([name, controller]) => {
    application.register(name, controller);
  });

  return application;
}
