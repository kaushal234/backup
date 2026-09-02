import {
  MIM_FETCH_MARKET_INTELLIGENCES,
  MIM_WRITE_MARKET_INTELLIGENCE,
} from "../../constants";

export function writeMarketIntelligence(marketIntelligence: any, form: any) {
  const url = marketIntelligence.id
    ? `/sales/market_intelligences/${marketIntelligence.id}`
    : `/sales/market_intelligences`;
  return {
    type: MIM_WRITE_MARKET_INTELLIGENCE,
    payload: {
      url,
      body: marketIntelligence,
      form,
    },
  };
}

export function fetchMarketIntelligences(search: any) {
  return {
    type: MIM_FETCH_MARKET_INTELLIGENCES,
    payload: {
      request: {
        url: `/sales/market_intelligences?normalizationGroupsOverride[]=market_intelligence:list&order[id]=asc&id=${search}`,
      },
    },
  };
}
