import {
  CONTRACT_AI_EXTERNAL_PARTY_TYPES,
  CONTRACT_RENEWAL_UNIT_OPTIONS,
} from "../constants/constants";
import { IContractAiData, IContractParty } from "../types/IContractAiData";
import { IContractAiFile } from "../types/IContractAiFile";
import { IContractFormData } from "../types/IContractFormData";
import { fetchAllCurrency } from "./dropdown/currency";
import { castToNumberAsString } from "./utils";

export function convertContractAiFileToFile(
  file: IContractAiFile
): File | null {
  if (!file.filename || !file.mimeType || !file.base64) {
    return null;
  }

  const byteString = atob(file.base64);
  const arrayBuffer = new Uint8Array(byteString.length);

  for (let i = 0; i < byteString.length; i++) {
    arrayBuffer[i] = byteString.charCodeAt(i);
  }

  const blob = new Blob([arrayBuffer], { type: file.mimeType });
  return new File([blob], file.filename, { type: file.mimeType });
}

export const isExternalParty = (party: IContractParty) => {
  return CONTRACT_AI_EXTERNAL_PARTY_TYPES.includes(party.partyRole ?? "");
};

export const mapAiDataToContractFormData = async (
  data?: IContractAiData
): Promise<IContractFormData> => {
  let currency = null;
  if (data?.currency) {
    const currencies = await fetchAllCurrency();
    const match = currencies.find(
      (item) => item.label.toLowerCase() === data.currency?.toLowerCase()
    );
    if (match) {
      currency = match;
    }
  }

  const renewalUnit = CONTRACT_RENEWAL_UNIT_OPTIONS.find(
    (unit) => unit.value.toLowerCase() === data?.renewalUnit?.toLowerCase()
  );

  const internalParty = (data?.parties ?? [])
    .filter((party) => !isExternalParty(party))
    .map((party) => party.partyName ?? "");

  const externalParty = (data?.parties ?? []).find(isExternalParty)?.partyName;

  return {
    shortDescription: data?.shortDescription ?? "",
    description: data?.description ?? "",
    startDate: data?.startDate ? new Date(data.startDate) : null,
    expirationDate: data?.expirationDate ? new Date(data.expirationDate) : null,
    jurisdiction: data?.jurisdiction ?? "",
    ...(data?.value && { value: castToNumberAsString(data?.value) }),
    ...(data?.renewalPeriod && {
      renewalPeriod: castToNumberAsString(data?.renewalPeriod),
    }),
    renewalUnit,
    currency,
    internalParty,
    externalParty,
  };
};
