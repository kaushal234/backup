import {
  ERP_FETCH_BUSINESS_PARTNER,
  ERP_FETCH_BUSINESS_PARTNERS,
  ERP_FETCH_SUPPLIERS,
} from "../../constants";

export function fetchBusinessPartner(suno: any) {
  return {
    type: ERP_FETCH_BUSINESS_PARTNER,
    payload: {
      request: {
        url: `/ion/business_partners/${suno}`,
      },
    },
  };
}

export function fetchBusinessPartners(search: any, filter = "") {
  let url = `/ion/business_partners?q=${search}`;
  if (filter !== "") {
    url += `&role=${filter}`;
  }
  return {
    type: ERP_FETCH_BUSINESS_PARTNERS,
    payload: {
      request: {
        url,
      },
    },
  };
}

export function fetchSageSuppliers(search: any) {
  return {
    type: ERP_FETCH_SUPPLIERS,
    payload: {
      request: {
        url: `/sageparts/suppliers?contains[name]=${search}`,
      },
    },
  };
}
