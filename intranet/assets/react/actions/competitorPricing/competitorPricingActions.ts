import { CPR_WRITE_COMPETITOR_PRICING } from "../../constants";

export function writeCompetitorPricing(comeptitorPricing: any, form: any) {
  return {
    type: CPR_WRITE_COMPETITOR_PRICING,
    payload: {
      url: `/sales/competitor_pricings`,
      body: comeptitorPricing,
      form,
    },
  };
}
