import { Controller } from "@hotwired/stimulus";
import { AutocompletePreConnectOptions } from "@symfony/ux-autocomplete";
import type { TomSelectElement } from "../../../types/tomselect";
import { fetch } from "../../utils/client";

export default class extends Controller<HTMLFormElement> {
  module!: HTMLSelectElement | null;

  projectManager!: TomSelectElement;

  private baseUrl?: string = process.env.WEBPACK_API_URI;

  initialize(): void {
    this.onPreConnect = this.onPreConnect.bind(this);
  }

  connect(): void {
    if (!this.baseUrl) {
      throw new Error("WEBPACK_API_URI is not defined");
    }

    const module = document.querySelector("#project_module");
    const projectManager = document.querySelector("#project_projectManager");

    if (!module || !projectManager) {
      throw new Error("Module or projectManager not found");
    }

    this.module = module as HTMLSelectElement;
    this.projectManager = projectManager as TomSelectElement;

    this.module.addEventListener(
      "autocomplete:pre-connect",
      this.onPreConnect as EventListener
    );
  }

  onPreConnect(event: CustomEvent<AutocompletePreConnectOptions>): void {
    const { options } = event.detail;
    const { baseUrl, projectManager } = this;

    options.onItemAdd = (value: string) => {
      const url = new URL(value, baseUrl);
      return fetch(url)
        .then((response: Response) => response.json())
        .then((json: any) => {
          const projectManagerSelect = projectManager.tomselect;

          projectManagerSelect.addOption({
            [projectManagerSelect.settings.valueField]:
              json.operationalOwner["@id"],
            [projectManagerSelect.settings
              .labelField]: `${json.operationalOwner.lastname} ${json.operationalOwner.firstname}`,
          });

          projectManagerSelect.addItem(json.operationalOwner["@id"]);
        })
        .catch((error) => {
          console.error("Failed to fetch people", {
            error,
            id: value,
          });
        });
    };
  }
}
