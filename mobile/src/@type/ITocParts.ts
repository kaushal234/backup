export type ITocParts = ITechnicianOnCall;

interface ITechnicianOnCall {
  parts: Array<ITechnicianOnCallPart>;
  sparePartsRequests: Array<ITocSparePartsRequest>;
}

interface ITechnicianOnCallPart {
  partNumber: string | null;
  vendorPartNumber: string | null;
  description: string;
  quantity: number;
}
interface ITocSparePartsRequest {
  parts: Array<ISparePartsRequestPart>;
}

interface ISparePartsRequestPart {
  partNumber: string;
  description: string;
  quantity: number;
}
