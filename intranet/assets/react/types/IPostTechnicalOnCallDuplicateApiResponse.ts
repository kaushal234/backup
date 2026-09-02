export type IPostTechnicalOnCallDuplicateApiResponse =
  ITechnicianOnCallDuplicateOutput;

export interface ITechnicianOnCallDuplicateOutput {
  "@context": unknown;
  "@type": string;
  "@id": string;
  outputLines: Array<ITechnicianOnCallDuplicateLineOutput>;
}

interface ITechnicianOnCallDuplicateLineOutput {
  "@type": string;
  "@id": string;
  serialNumber: string;
  technicianOnCallId: number;
  technicianOnCallErrorMessage: string | null;
  customerServiceRecordId: number | null;
  customerServiceRecordErrorMessage: string | null;
}
