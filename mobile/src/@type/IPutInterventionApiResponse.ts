export type IPutInterventionApiResponse = IIntervention;

export interface IIntervention {
  "@context": string;
  "@id": string;
  "@type": string;
  startedAt: string | null;
  endedAt: string | null;
  plannedBy: IPeople | null;
  plannedAt: string;
  comments: string | null;
  customerServiceRecord: string;
  leader: IPeople;
  operators: Array<unknown>;
  status: string;
  id: number | null;
}

export interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  hidden: boolean;
  disabled: boolean;
  passwordUpdatedAt: string | null;
  email: string;
  photo: string | null;
  firstname: string | null;
  lastname: string | null;
  passwordExpirationDate: string | null;
}
