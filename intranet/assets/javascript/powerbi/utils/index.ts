import { Embed } from "powerbi-client";
import {
  REPORT_FULLSCREEN_BUTTON_CLASSNAME,
  REPORT_IN_FILTER,
} from "../constants";
import { IFilter } from "../types/IFilter";

export const getErrorMessage = (error: any): string => {
  let errorMessage = "default error";
  if (typeof error === "string") {
    try {
      errorMessage = JSON.parse(error);
    } catch (parseError) {
      // empty on purpose
    }
  }
  if (error.status) {
    errorMessage = error.status;
  }
  if (error.statusText) {
    errorMessage = error.statusText;
  }
  if (error.response) {
    if (error.response?.data) {
      try {
        errorMessage = JSON.parse(error.response.data);
      } catch (e) {
        // empty on purpose
      }
    }
  }
  return errorMessage;
};

export const showError = (
  reportContainer: HTMLElement | null,
  error: string
) => {
  if (!reportContainer) return;
  const errorElement = document.createElement("div");
  errorElement.innerHTML = `
    <section class="error-container d-block">
      <p>
        <strong>Error Details :</strong>
      </p>
      <p>
        ${error}
      </p>
    </section>`;
  reportContainer.replaceChildren(errorElement);
};

export const addFullScreenButton = (
  reportContainer: HTMLElement | null,
  report: Embed
) => {
  if (
    !reportContainer ||
    reportContainer.querySelector(`.${REPORT_FULLSCREEN_BUTTON_CLASSNAME}`)
  ) {
    return;
  }

  const fullscreenButton = document.createElement("button");
  fullscreenButton.textContent = "Switch to Fullscreen Mode";
  fullscreenButton.className = `btn btn-sm btn-outline-secondary float-end ${REPORT_FULLSCREEN_BUTTON_CLASSNAME}`;
  fullscreenButton.style.marginBottom = "8px";

  reportContainer.prepend(fullscreenButton);

  fullscreenButton.addEventListener("click", () => {
    report.fullscreen();
  });
};

export const getUrlParam = (name: string) => {
  const params = new URLSearchParams(window.location.search);
  return params.get(name);
};

export const getUrlParamArray = (name: string) => {
  const params = new URLSearchParams(window.location.search);
  return params.getAll(name);
};

export const getLocationFilter = (reportContainer?: HTMLElement) => {
  const result: Array<IFilter> = [];
  const data = reportContainer?.dataset;
  const location = getUrlParamArray("location");
  const table = getUrlParam("table") ?? data?.locationTable;
  const column = getUrlParam("column") ?? data?.locationColumn;
  const hideFilter = getUrlParam("hideFilter");
  if (table && column && location.length > 0) {
    const locationFilter: IFilter = {
      ...REPORT_IN_FILTER,
      target: {
        table,
        column,
      },
      values: location,
      displaySettings: {
        isHiddenInViewMode: hideFilter === "true",
      },
    };
    result.push(locationFilter);
  }
  return result;
};

export const printFiltersToConsole = async (report: Embed) => {
  const reportFilters = await (report as any).getFilters();
  console.info("Report Filters:", reportFilters);
  const pages = await (report as any).getPages();
  await Promise.all(
    pages.map(async (page: any) => {
      const pageFilters = await page.getFilters();
      console.info(`Page Filters [${page.name}]:`, pageFilters);
      const visuals = await page.getVisuals();
      await Promise.all(
        visuals.map(async (visual: any) => {
          const visualFilters = await visual.getFilters();
          if (visualFilters.length > 0) {
            console.info(`Visual Filters [${visual.name}]:`, visualFilters);
          }
        })
      );
    })
  );
};
