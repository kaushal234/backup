import axios from "axios";

const { models, service, factories } = window["powerbi-client"];

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface ITarget {
  table: string;
  column: string;
}

interface IReportFilter {
  $schema: string;
  target: ITarget;
  filterType: number;
  operator: string;
  values: Array<string | number>;
  requireSingleSelection: boolean;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const REPORT_CONTAINER_CLASSNAME = "report-container";
const POWER_BI_URL =
  window.APP_CONFIG?.POWERBI_URL || "https://localhost:8082";

const REPORT_NUMBER_AND_ID_MAP: Record<number, string | undefined> = {
  64: window.APP_CONFIG?.POWER_BI_REPORT_64,
};

const REPORT_FILTER_MAP: Record<string, Array<IReportFilter>> = {
  POWER_BI_REPORT_64: [
    {
      $schema: "http://powerbi.com/product/schema#basic",
      target: {
        table: "public v_bi41_otdp",
        column: "businessunit",
      },
      filterType: 1,
      operator: "In",
      values: [],
      requireSingleSelection: false,
    },
    {
      $schema: "http://powerbi.com/product/schema#basic",
      target: {
        table: "public v_bi41_otdp",
        column: "businesspartnercode",
      },
      filterType: 1,
      operator: "In",
      values: [],
      requireSingleSelection: false,
    },
  ],
};

// Re-key the filter map from its report-number placeholder to the actual
// report id resolved from the runtime configuration.
Object.entries(REPORT_NUMBER_AND_ID_MAP).forEach(
  ([reportNumber, reportId = ""]) => {
    const filter = REPORT_FILTER_MAP[`POWER_BI_REPORT_${reportNumber}`];
    delete REPORT_FILTER_MAP[`POWER_BI_REPORT_${reportNumber}`];
    REPORT_FILTER_MAP[reportId] = filter;
  },
);

// ---------------------------------------------------------------------------
// HTTP client (internal API)
// ---------------------------------------------------------------------------

const http = axios.create({
  baseURL: window.APP_CONFIG?.INTERNAL_API_ENDPOINT || "https://localhost:8080",
});

http.interceptors.request.use(
  async (config) => {
    if (window.user?.token) {
      config.headers.Authorization = `Bearer ${window.user.token}`;
      config.headers.Accept = "application/json";
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// ---------------------------------------------------------------------------
// API calls
// ---------------------------------------------------------------------------

const getEmbedData = async (reportId: string) => {
  return http.get(`${POWER_BI_URL}/powerbi/getEmbedToken`, {
    params: {
      reportId,
    },
  });
};

const getErpByLocation = async (
  locationIri?: string,
): Promise<Array<string>> => {
  try {
    if (locationIri?.toLowerCase() === "all") return [];
    const locationId = locationIri?.slice(11);
    const response = await http.get(`/locations/${locationId}`);
    return response?.data?.erp ? [response?.data?.erp?.toString()] : [];
  } catch (error) {
    console.log(error);
    return [];
  }
};

// ---------------------------------------------------------------------------
// Utils
// ---------------------------------------------------------------------------

const getErrorMessage = (error: any): string => {
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

const showError = (reportContainer: HTMLElement | null, error: string) => {
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

const getFilters = (
  reportId: string,
  filterValues: Record<string, Array<string>>,
) => {
  const match = REPORT_FILTER_MAP[reportId];

  if (!match) {
    return [];
  }

  const result: Array<IReportFilter> = [];

  match.forEach((filter) => {
    Object.keys(filterValues).forEach((filterValueKey) => {
      if (
        filter?.target?.column === filterValueKey &&
        filterValues[filterValueKey].length
      ) {
        const newFilter = { ...filter };
        newFilter.values = filterValues[filterValueKey];
        result.push(newFilter);
      }
    });
  });

  return result;
};

const updateReportHeight = (
  reportContainer: HTMLElement | null,
  height: string,
) => {
  if (!reportContainer) return;
  const iframe = reportContainer.querySelector("iframe");
  if (!iframe) return;
  iframe.style.height = height;
};

// ---------------------------------------------------------------------------
// Report rendering
// ---------------------------------------------------------------------------

const renderReport = async (reportContainer: HTMLElement) => {
  const reportId = reportContainer?.dataset?.reportId;
  const supplier = reportContainer?.dataset?.supplier;
  const factory = reportContainer?.dataset?.factory;

  if (reportContainer && reportId) {
    try {
      const powerbi = new service.Service(
        factories.hpmFactory,
        factories.wpmpFactory,
        factories.routerFactory,
      );

      powerbi.bootstrap(reportContainer, { type: "report" });

      const response = await getEmbedData(reportId);

      const erp = await getErpByLocation(factory);
      const filterValues = {
        businessunit: erp,
        businesspartnercode: supplier ? [supplier] : [],
      };

      const reportLoadConfig = {
        type: "report",
        tokenType: models.TokenType.Embed,
        accessToken: response.data?.accessToken,
        embedUrl: response.data?.embedUrl?.[0]?.embedUrl,
        settings: {
          filterPaneEnabled: false,
        },
        filters: getFilters(reportId, filterValues),
      };

      const report = powerbi.embed(reportContainer, reportLoadConfig);

      report.off("error");
      report.off("rendered");

      report.on("error", (event) => console.error(event.detail));
      report.on("rendered", () => updateReportHeight(reportContainer, "600px"));
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
    `.${REPORT_CONTAINER_CLASSNAME}`,
  );
  Array.from(reportContainers).forEach((reportContainer) => {
    if (reportContainer instanceof HTMLElement) {
      renderReport(reportContainer);
    }
  });
};

initialize();
