import { models, service, factories, Embed, Report } from "powerbi-client";
import {
  addFullScreenButton,
  getErrorMessage,
  getLocationFilter,
  getUrlParam,
  showError,
} from "./utils";
import { getEmbedData } from "./api/getEmbedData";
import { REPORT_CONTAINER_CLASSNAME } from "./constants";
import { IFilter } from "./types/IFilter";

const setActiveReportPage = async (report: Embed, pageName = "") => {
  try {
    const pages = await (report as unknown as Report).getPages();
    const match = pages.find(
      (page) => page.name === pageName || page.displayName === pageName
    );
    if (match) {
      await match.setActive();
    }
  } catch (err) {
    console.error("Failed to set default page:", err);
  }
};

const renderReport = async (reportContainer: HTMLElement) => {
  const reportId = reportContainer?.dataset?.reportId;
  const workspaceId = reportContainer?.dataset?.workspaceId;
  const page = reportContainer?.dataset?.page;

  if (reportContainer && reportId) {
    try {
      const powerbi = new service.Service(
        factories.hpmFactory,
        factories.wpmpFactory,
        factories.routerFactory
      );

      powerbi.bootstrap(reportContainer, { type: "report" });

      const response = await getEmbedData(reportId, workspaceId);

      const filters: Array<IFilter> = [...getLocationFilter(reportContainer)];

      const reportLoadConfig = {
        type: "report",
        tokenType: models.TokenType.Embed,
        accessToken: response.data?.accessToken,
        embedUrl: response.data?.embedUrl?.[0]?.embedUrl,
        filters,
        settings: {
          filterPaneEnabled: getUrlParam("hideFilterPane") !== "true",
        },
      };

      const report = powerbi.embed(reportContainer, reportLoadConfig);

      report.off("loaded");
      report.off("error");

      report.on("loaded", () => {
        addFullScreenButton(reportContainer, report);
        setTimeout(() => {
          setActiveReportPage(report, page);
        }, 1000);
      });
      report.on("error", (event) => console.error(event.detail));

      report.on("rendered", async () => {
        // use printFiltersToConsole function to log all the filters applied to report
      });
    } catch (err) {
      console.error("Error: ", err);
      showError(reportContainer, getErrorMessage(err));
    }
  } else {
    showError(reportContainer, "data-report-id is not defined.");
  }
};

const initialize = () => {
  const reportContainers = document.querySelectorAll(
    `.${REPORT_CONTAINER_CLASSNAME}`
  );
  Array.from(reportContainers).forEach((reportContainer) => {
    if (reportContainer instanceof HTMLElement) {
      renderReport(reportContainer);
    }
  });
};

initialize();
