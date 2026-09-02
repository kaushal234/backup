export type IGetManualDocumentResponse = IManualDocument;

export interface IManualDocument {
  "@context": string;
  "@id": string;
  "@type": string;
  manual: IManual | null;
  position: number;
  factoryNumber: string | null;
  revision: string | null;
  category: IManualDocumentCategory | null;
  type: string;
  description: string | null;
  otherDescription: string | null;
  quantity: number;
  createdAt: string | null;
  createdBy: IPeople | null;
  parts: Array<IManualPart>;
  id: number;
  legacyId: number | null;
  document: IManualDocumentFile | null;
}

interface IManualDocumentCategory {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}

interface IManualPart {
  "@id": string;
  "@type": string;
  position: number;
  partNumber: string;
  quantity: number;
  unitOfMeasure: string | null;
  description: string | null;
  otherDescription: string | null;
  preventive: boolean;
  maintenance: boolean;
  overhaul: boolean;
  critical: boolean;
  id: number;
  legacyId: number | null;
}

interface IManualDocumentFile {
  "@id": string;
  "@type": string;
  public: boolean;
  id: number;
  filePath: string;
  poster: IPeople | null;
  createdAt: string;
  description: string | null;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
}

interface IManual {
  "@id": string;
  "@type": string;
  equipmentRecord: IEquipmentRecord | null;
  language: string | null;
  id: number | null;
  legacyId: number | null;
}

interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  serialNumber: string;
  buyer: ICustomer | null;
  model: string | null;
  type: string | null;
  location: string | null;
  dateShipped: string | null;
  manufacturerLocation: ILocation | null;
  publishable: boolean;
  greenTagDate: string | null;
  legacyId: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}
