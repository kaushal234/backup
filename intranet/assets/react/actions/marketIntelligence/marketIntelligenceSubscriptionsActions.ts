import { MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION } from "../../constants";

export function writeMarketIntelligenceSubscription(
  marketIntelligenceSubscription: any,
  form: any
) {
  return {
    type: MIM_WRITE_MARKET_INTELLIGENCE_SUBSCRIPTION,
    payload: {
      url: `/sales/market_intelligence_subscriptions`,
      body: marketIntelligenceSubscription,
      form,
    },
  };
}
