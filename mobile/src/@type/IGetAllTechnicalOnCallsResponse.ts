import { IHydraCollection } from "./IHydraCollection";

export type IGetAllTechnicalOnCallsResponse =
  IHydraCollection<ITechnicianOnCall>;

export interface ITechnicianOnCall {
  "@id": string;
  "@type": string;
  title: string | null;
  description: string | null;
  status: string | null;
  equipmentRecord: IEquipmentRecord | null;
  id: number | null;
  indiceFactor: string;
  airport: IAirport;
  mainFile: ITechnicianOnCallMainFile | null;
  openDaysStatus: "ON_TIME" | "OUTDATED";
  factoryFlag: boolean;
  originalDescription: string | null;
  originalTitle: string | null;
}

interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  serialNumber: string;
  buyer: string | null;
  endUser: ICustomer | null;
  maintainer: string | null;
  model: string | null;
  type: string | null;
  product: string | null;
  airport: string | null;
  location: string | null;
  parentEquipment: string | null;
  childrenEquipments: Array<string> | null;
  estimatedGreenTagDate: string | null;
  dateCommissioned: string | null;
  manufacturerLocation: string | null;
  salesOrganisation: string | null;
  salesOrganisationService: string | null;
  greenTagDate: string | null;
  hourMeter: number | null;
}

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  cityName: string;
}

interface ITechnicianOnCallMainFile {
  "@id": string;
  "@type": string;
  id: number;
  filePath: string;
  createdAt: string;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  address: Array<unknown> | null;
  country: unknown | null;
  phone: string | null;
  fax: string | null;
}
