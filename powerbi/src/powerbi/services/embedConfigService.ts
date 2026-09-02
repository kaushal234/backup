import fetch from "node-fetch";
import { getAccessToken } from "../auth/authentication.ts";
import { getAuthHeader } from "../utils/utils.ts";
import { IConfig } from "../@types/IConfig.ts";
import { IError } from "../@types/IError.ts";
import { IGetEmbedParamsResponse } from "../@types/IGetEmbedParamsResponse.ts";
import { IFormData } from "../@types/IFormData.ts";
import { IPowerBiReportDetails } from "../@types/IPowerBiReportDetails.ts";
import { IEmbedConfig } from "../@types/IEmbedConfig.ts";
import { IGetEmbedTokenResponse } from "../@types/IGetEmbedTokenResponse.ts";

/**
 * Get Request header
 * @return Request header with Bearer token
 */
const getRequestHeader = async (config: IConfig = {}) => {
  // Store authentication token
  let tokenResponse;

  // Store the error thrown while getting authentication token
  let errorResponse;

  // Get the response from the authentication request
  try {
    tokenResponse = await getAccessToken(config);
  } catch (error: unknown) {
    const err = error as IError;
    if (err?.error_description && err?.error) {
      errorResponse = err.error_description;
    } else {
      // Invalid PowerBI Username provided
      errorResponse = err.toString();
    }
    return {
      status: 401,
      error: errorResponse,
    };
  }

  // Extract AccessToken from the response
  const token = tokenResponse?.accessToken ?? "";
  return {
    "Content-Type": "application/json",
    Authorization: getAuthHeader(token),
  };
};

/**
 * Get Embed token for single report, multiple datasets, and an optional target workspace
 * @param {Object} config
 * @param {Array<string>} datasetIds
 * @param {string} targetWorkspaceId - Optional Parameter
 * @return EmbedToken
 */
const getEmbedTokenForSingleReportSingleWorkspace = async (
  config: IConfig,
  datasetIds: Array<string>,
  targetWorkspaceId = ""
): Promise<IGetEmbedTokenResponse> => {
  // Add report id in the request
  const formData: IFormData = {
    reports: [
      {
        id: config.reportId ?? "",
      },
    ],
  };

  // Add dataset ids in the request
  formData.datasets = datasetIds.map((datasetId) => ({
    id: datasetId,
  }));

  // Add targetWorkspace id in the request
  if (targetWorkspaceId) {
    formData.targetWorkspaces = [];
    formData.targetWorkspaces.push({
      id: targetWorkspaceId,
    });
  }

  const embedTokenApi = "https://api.powerbi.com/v1.0/myorg/GenerateToken";
  const headers = await getRequestHeader(config);

  // Generate Embed token for single report, workspace, and multiple datasets. Refer https://aka.ms/MultiResourceEmbedToken
  const result = await fetch(embedTokenApi, {
    method: "POST",
    headers: {
      "Content-Type": headers["Content-Type"] ?? "",
      Authorization: headers.Authorization ?? "",
    },
    body: JSON.stringify(formData),
  });

  if (!result.ok) throw result;
  return result.json() as Promise<IGetEmbedTokenResponse>;
};

/**
 * Get embed params for a single report for a single workspace
 * @param config
 * @param {string} additionalDatasetId - Optional Parameter
 * @return EmbedConfig object
 */
const getEmbedParamsForSingleReport = async (
  config: IConfig,
  additionalDatasetId = ""
) => {
  const reportInGroupApi = `https://api.powerbi.com/v1.0/myorg/groups/${config.workspaceId}/reports/${config.reportId}`;
  const headers = await getRequestHeader(config);

  // Get report info by calling the PowerBI REST API
  const result = await fetch(reportInGroupApi, {
    method: "GET",
    headers: {
      "Content-Type": headers["Content-Type"] ?? "",
      Authorization: headers.Authorization ?? "",
    },
  });

  if (!result.ok) {
    throw result;
  }

  // Convert result in json to retrieve values
  const resultJson = (await result.json()) as IGetEmbedParamsResponse;

  // Add report data for embedding
  const reportDetails: IPowerBiReportDetails = {
    reportId: resultJson.id,
    reportName: resultJson.name,
    embedUrl: resultJson.embedUrl,
  };
  const reportEmbedConfig: IEmbedConfig = {};

  // Create mapping for report and Embed URL
  reportEmbedConfig.reportsDetail = [reportDetails];

  // Create list of datasets
  const datasetIds = [resultJson.datasetId ?? ""];

  // Append additional dataset to the list to achieve dynamic binding later
  if (additionalDatasetId) {
    datasetIds.push(additionalDatasetId);
  }

  // Get Embed token multiple resources
  reportEmbedConfig.embedToken =
    await getEmbedTokenForSingleReportSingleWorkspace(config, datasetIds);
  return reportEmbedConfig;
};

/**
 * Generate embed token and embed urls for reports
 * @return Details like Embed URL, Access token and Expiry
 */
export const getEmbedInfo = async (config: IConfig) => {
  // Get the Report Embed details
  try {
    // Get report details and embed token
    const embedParams = await getEmbedParamsForSingleReport(config);

    return {
      accessToken: embedParams.embedToken?.token,
      embedUrl: embedParams.reportsDetail,
      expiry: embedParams.embedToken?.expiration,
      status: 200,
    };
  } catch (error: unknown) {
    const err = error as IError;
    return {
      status: err.status ?? 500,
      error: `Error while retrieving report embed details \r\n ${
        err.statusText
      } \r\n RequestId: \n ${err.headers?.get("requestid")}`,
    };
  }
};
