export type IPostTechnicalOnCallPartsApiResponse = ITechnicianOnCallPart;

interface ITechnicianOnCallPart {
  "@context": string;
  "@id": string;
  "@type": string;
  partNumber: string | null;
  vendorPartNumber: string | null;
  serialNumber: string | null;
  description: string;
  quantity: number;
  createdAt: string;
  deletedAt: string | null;
  createdBy: string;
  defective: boolean;
  supplierReplaces: boolean;
  customerReplaces: boolean;
  returnRequired: boolean;
  quotationRequired: boolean;
  comment: string;
  id: number | null;
  technicianOnCall: string;
}
