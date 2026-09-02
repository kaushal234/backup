import { IGetEmbedTokenResponse } from "./IGetEmbedTokenResponse.ts";
import { IPowerBiReportDetails } from "./IPowerBiReportDetails.ts";

export interface IEmbedConfig {
  reportsDetail?: Array<IPowerBiReportDetails>;
  embedToken?: IGetEmbedTokenResponse;
}
