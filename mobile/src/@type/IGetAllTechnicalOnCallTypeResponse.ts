import { IHydraCollection } from "./IHydraCollection";

export type IGetAllTechnicalOnCallTypeResponse =
  IHydraCollection<ITechnicianOnCallType>;

interface ITechnicianOnCallType {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  id: number | null;
}
