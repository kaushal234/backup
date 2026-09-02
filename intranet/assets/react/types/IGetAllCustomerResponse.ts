import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCustomerResponse = IHydraCollection<ICustomer>;

export interface ICustomer {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  address: IAddress;
  country: string | null;
  mainSalesRepresentative: string | null;
  status: string;
  customerTypes: Array<ICustomerType>;
  legacyId: number;
}

interface IAddress {
  "@type": string;
  "@id": string;
  street1: string | null;
  street2: string | null;
  postalCode: string | null;
  city: string | null;
  town: string | null;
  state: string | null;
}

interface ICustomerType {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number;
}
