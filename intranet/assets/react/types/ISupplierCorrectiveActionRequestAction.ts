export interface ISupplierCorrectiveActionRequestAction {
  id?: number | null;
  factory: string;
  iFactor: string;
  shortDescription: string;
  description: string;
  representative: string;
  leader: string | null;
  supplierNumber: string;
  issueOrigin?: string | null;
  correctiveAction?: string | null;
  preventiveAction?: string | null;
  commercialAgreement?: string | null;
  verificationDescription?: string | null;
  conclusion?: string | null;
}
