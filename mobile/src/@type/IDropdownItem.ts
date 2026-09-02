import { IExtranetUser } from "./IGetAllContactResponse";
import { IItemMonologistic } from "./IGetAllItemMonologisticsResponse";

export interface IDropdownItem {
  id: string;
  text: string;
  infoText?: string;
  additionalInfo?:
    | {
        type: "EquipmentRecord";
        data: IEquipmentRecord;
      }
    | {
        type: "ItemMonologistic";
        data: IItemMonologistic;
      }
    | {
        type: "Country";
        data: ICountry;
      }
    | {
        type: "ExtranetUser";
        data: IExtranetUser;
      };
}

export interface IEquipmentRecord {
  "@id": string;
  airport: IAirport | null;
  salesOrganisationService: ILocation | null;
  hourMeter: number | null;
  endUser: ICustomer | null;
  customerSerialNumber?: string | null;
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

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  legacyId: number | null;
  type: string;
  id: number;
  cityCode3: string | null;
  cityName: string;
  state: string | null;
  country: ICountry | null;
  name: string | null;
  source: string;
}

export interface ICountry {
  "@id": string;
  "@type": string;
  name: string | null;
  isoCode2: string;
  phoneCode?: string | null;
}
