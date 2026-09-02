import { IHydraCollection } from "./IHydraCollection";

export type IGetAllEquipmentSerialResponse = IHydraCollection<IEquipmentSerial>;

export interface IEquipmentSerial {
  "@id": string;
  "@type": string;
  component: IEquipmentSerialComponent | null;
  model: string | null;
  serial: string | null;
  brand: string | null;
  createdAt: string | null;
  createdBy: IPeople | null;
  id: number;
  legacyId: number | null;
}

interface IEquipmentSerialComponent {
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
