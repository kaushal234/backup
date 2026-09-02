import { createSelector } from "reselect";
import moment from "moment";
import { RootState } from "../../store";

const getSalesForecasts = (state: RootState) => {
  return state.sfr.salesForecasts;
};

export const getSalesForecastsMapping = createSelector(
  [getSalesForecasts],
  (salesForecasts) => {
    if (!salesForecasts) {
      return [];
    }
    return Object.values(salesForecasts).map((salesForecast: any, index) => ({
      ...salesForecast,
      estimatedSaleDate: moment(salesForecast.estimatedSaleDate).toDate(),
      index,
    }));
  }
);

const getSalesForecastsComments = (state: RootState, sfrIri: any) => {
  return state.sfr.comments[sfrIri];
};

export const getSalesForecastsCommentsMapping = createSelector(
  [getSalesForecastsComments],
  (comments) => {
    if (!comments) {
      return null;
    }
    return Object.values(comments).map((comment: any, index) => ({
      ...comment,
      createdAt: moment(comment.createdAt).toDate(),
      index,
    }));
  }
);
