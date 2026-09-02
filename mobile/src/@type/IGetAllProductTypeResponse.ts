import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProductTypeResponse = IHydraCollection<IProductType>;

interface IProductType {
  "@id": string;
  "@type": string;
  id: number;
  englishName: string | null;
  frenchName: string | null;
  spanishName: string | null;
  portugueseName: string | null;
  chineseName: string | null;
  japaneseName: string | null;
  germanName: string | null;
  russianName: string | null;
  dms: IDms | null;
  publicForTLD: boolean;
  publicForAerospecialties: boolean;
  publicForSAS: boolean;
  legacyId: number | null;
}

interface IDms {
  "@id": string;
  "@type": string;
  id: number;
  title: string;
  subject: string;
  description: string;
  language: string;
  portal: string | null;
  type: string | null;
  createdAt: string | null;
  owner: IPeople | null;
  status: string | null;
  legacyId: number | null;
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
