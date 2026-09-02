export interface IContractAiData {
  shortDescription: string | null;
  description: string | null;
  startDate: string | null;
  expirationDate: string | null;
  jurisdiction: string | null;
  value: number | null;
  currency: string | null;
  renewalPeriod: number | null;
  renewalUnit: string | null;
  parties: Array<IContractParty> | null;
  message: string | null;
}

export interface IContractParty {
  partyName?: string;
  partyRole?: string;
}
