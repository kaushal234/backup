import {
  FINANCE_CREATE_FINANCE_FAMILY,
  FINANCE_DELETE_FINANCE_FAMILY,
  FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES,
  FINANCE_REMOVE_PRICING,
  FINANCE_UPDATE_FINANCE_FAMILY,
} from "../../constants";

export function writeFinanceFamily(financeFamily: any, index: any) {
  let url = "/finance/finance_families";
  let type = FINANCE_CREATE_FINANCE_FAMILY;
  if (financeFamily.id) {
    url += `/${financeFamily.id}`;
    type = FINANCE_UPDATE_FINANCE_FAMILY;
  }

  return {
    type,
    payload: {
      url,
      body: financeFamily,
    },
    index,
  };
}

export function handleFinanceFamilyFactories(
  financeFamily: any,
  factory: any,
  value: any,
  index: any,
  sso: any
) {
  return {
    type: FINANCE_HANDLE_FINANCE_FAMILY_FACTORIES,
    financeFamily,
    index,
    factory,
    value,
    sso,
  };
}

export function removePricing(
  financeFamily: any,
  sso: any,
  factory: any,
  index: any
) {
  return {
    type: FINANCE_REMOVE_PRICING,
    financeFamily,
    sso,
    factory,
    index,
  };
}

export function deleteFinanceFamily(id: any, index?: any) {
  return {
    type: FINANCE_DELETE_FINANCE_FAMILY,
    payload: {
      url: `/finance/finance_families/${id}`,
      index,
    },
  };
}
