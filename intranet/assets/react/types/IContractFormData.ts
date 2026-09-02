import { IDropdownItem } from "./IDropdownItem";

export interface IContractFormData {
  shortDescription?: string;
  category?: IDropdownItem | null;
  subCategory?: IDropdownItem | null;
  description?: string;
  parentContract?: IDropdownItem | null;
  jurisdiction?: string;
  startDate?: Date | null;
  signatureDate?: Date | null;
  divisions?: Array<IDropdownItem>;
  regions?: Array<IDropdownItem>;
  businessUnits?: Array<IDropdownItem>;
  premises?: Array<IDropdownItem>;
  customers?: Array<IDropdownItem>;
  value?: string;
  currency?: IDropdownItem | null;
  expirationDate?: Date | null;
  renewalPeriod?: string;
  renewalUnit?: IDropdownItem | null;
  indefinitePeriodType?: boolean;
  automaticRenewal?: boolean;
  confidential?: boolean;
  observationTerm?: string | null;
  observationValue?: string | null;
  externalParty?: string;
  internalParty?: Array<IContractInternalPartyFormData>;
  otherPartySignatories?: Array<IContractOtherPartySignatoriesFormData>;
  owner?: IDropdownItem | null;
  status?: IDropdownItem | null;
  comment?: string | null;
}

export type IContractInternalPartyFormData = string | undefined;
export type IContractOtherPartySignatoriesFormData = string | undefined;
