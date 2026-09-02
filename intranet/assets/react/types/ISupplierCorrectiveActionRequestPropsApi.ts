export interface ISupplierCorrectiveActionRequestPropsApi {
  supplierCorrectiveActionRequest: ISupplierCorrectiveActionRequestApi;
}

export interface ISupplierCorrectiveActionRequestApi {
  id?: number;
  factory: IFactoryApi;
  iFactor: string;
  shortDescription: string;
  description: string;
  representative: IRepresentativeApi;
  leader?: ILeaderApi | null;
  supplierNumber: string;
  supplierName: string;
  issueOrigin?: string | null;
  correctiveAction?: string | null;
  preventiveAction?: string | null;
  commercialAgreement?: string | null;
  verificationDescription?: string | null;
  conclusion?: string | null;
  mainFile: ISupplierCorrectiveActionRequestMainFile | null;
}

export interface IFactoryApi {
  "@id": string;
  "@type": string;
  name: string;
  erp: number;
}

interface ILeaderApi {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
}
interface IRepresentativeApi {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
}

export interface ISupplierCorrectiveActionRequestMainFile {
  "@id": string;
  "@type": string;
  public: boolean;
  id: number | null;
  filePath: string;
  poster: IPeople;
  createdAt: string;
  description: string | null;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
}
