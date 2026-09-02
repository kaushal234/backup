import { IFactoryApi } from "./ISupplierCorrectiveActionRequestPropsApi";

export interface ISupplierCorrectiveActionRequestPrefillProps {
  prefilledDataFromNonConformityRecord: ISupplierCorrectiveActionRequestPrefill;
}

export interface ISupplierCorrectiveActionRequestPrefill {
  factory?: IFactoryApi | null;
  shortDescription?: string | null;
  description?: string | null;
}
