export interface IGetEmbedParamsResponse {
  "@odata.context": string;
  id: string;
  reportType: string;
  name: string;
  webUrl: string;
  embedUrl: string;
  isFromPbix: boolean;
  isOwnedByMe: boolean;
  datasetId: string;
  datasetWorkspaceId: string;
  users: Array<unknown>;
  subscriptions: Array<unknown>;
  reportFlags: number;
}
