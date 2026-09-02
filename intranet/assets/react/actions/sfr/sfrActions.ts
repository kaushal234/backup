import {
  SFR_UPDATE_SALES_FORECAST,
  SFR_UPDATE_SALES_FORECASTS_COMPLETED,
  SFR_UPDATE_SALES_FORECASTS,
  SFR_GET_SALES_FORECAST_COMMENTS,
  SFR_GET_SALES_FORECAST_VALORIZATION,
  SFR_CREATE_MASTER_SALES_FORECAST,
} from "../../constants";

export function updateSalesForecast(
  salesForecast: any,
  errors: any,
  form: any,
  index?: any
) {
  return {
    type: SFR_UPDATE_SALES_FORECAST,
    payload: {
      form,
      url: `/sales/sales_forecasts/${salesForecast.id}`,
      body: salesForecast,
      errors,
      index,
    },
  };
}

export function updateSalesForecasts(
  salesForecasts: any,
  errors: any,
  form: any
) {
  return {
    type: SFR_UPDATE_SALES_FORECASTS,
    payload: {
      form,
      salesForecasts,
      errors,
    },
  };
}

export function writeMasterSalesForecast(
  masterSalesForecast: any,
  form: any,
  index: any,
  allSubmitted: any,
  errors: any
) {
  return {
    type: SFR_CREATE_MASTER_SALES_FORECAST,
    payload: {
      form,
      url: `/sales/master_sales_forecasts`,
      body: masterSalesForecast,
      index,
      errors,
      allSubmitted,
    },
  };
}

export function getSalesForecastComments(salesForecast: any) {
  return {
    type: SFR_GET_SALES_FORECAST_COMMENTS,
    payload: {
      url: `/comments?resource=${salesForecast["@id"]}&pagination=false`,
      sfrIri: salesForecast["@id"],
    },
  };
}

export function updateSalesForecastsCompleted() {
  return {
    type: SFR_UPDATE_SALES_FORECASTS_COMPLETED,
  };
}

export function getValorizationForSalesForecast(
  financeFamilyIri: any,
  factoryIri: any,
  ssoIri: any,
  index: any
) {
  return {
    type: SFR_GET_SALES_FORECAST_VALORIZATION,
    payload: {
      url: `/finance/finance_family_pricings?financeFamily=${financeFamilyIri}&factory=${factoryIri}&sso=${ssoIri}`,
      index,
    },
  };
}
