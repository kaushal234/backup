import { IHydraCollection } from "./IHydraCollection";

export type IGetAuditLogResponse = IHydraCollection<IAuditLogProperty>;

export interface IAuditLogProperty {
  "@type": string;
  "@id": string;
  value: string;
  time: number | null;
}
