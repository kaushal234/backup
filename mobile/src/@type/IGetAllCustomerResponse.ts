import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCustomerResponse = IHydraCollection<ICustomer>;

interface ICustomer {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  address: Array<IAddress> | null;
  country: unknown | null;
  mainSalesRepresentative: null;
  status: string;
  customerTypes: Array<ICustomerType>;
  legacyId: number | null;
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
  legacyId: number | null;
}
