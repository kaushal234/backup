export interface ISupplierCorrectiveActionRequestFactoryProps {
  id?: number;
  factory: IFactory;
  importanceFactor: string;
  shortDescription: string;
  description: string;
  representative: IRepresentative;
  leader: ILeader | null;
  supplierNumber: ISupplierNumber;
  issueOrigin?: string | null;
  correctiveAction?: string | null;
  preventiveAction?: string | null;
  commercialAgreement?: string | null;
  verificationDescription?: string | null;
  conclusion?: string | null;
}

export interface IFactory {
  erp: number;
  value: string;
  label: string;
}

interface IRepresentative {
  value: string;
  label: string;
}

interface ILeader {
  value: string;
  label: string | null;
}

interface ISupplierNumber {
  value: string;
  name: string;
  label: string;
}
