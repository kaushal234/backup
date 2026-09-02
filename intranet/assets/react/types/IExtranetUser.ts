export interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  disabled?: boolean;
  extranetUserProfile: IExtranetUserProfile;
  firstname: string;
  lastname: string;
  erpLocation?: IErpLocation;
  phones: Array<string>;
  hidden: boolean;
  passwordUpdatedAt: string;
  legacyId: number;
  id: number;
  passwordExpirationDate: string;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  customer: ICustomer;
  legacyId?: number;
  department?: string;
  id?: number;
  erpLocation?: IErpLocation;
}

interface IErpLocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number;
  legacyId: number;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  mainSalesRepresentative: IMainSalesRepresentative | null;
  name: string;
  status: string;
  legacyId: number;
}

interface IMainSalesRepresentative {
  "@id": string;
  "@type": string;
  asm: IAsm;
}

interface IAsm {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  photo: string | null;
  firstname: string;
  lastname: string;
}

interface IErpLocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number;
  legacyId: number;
}
