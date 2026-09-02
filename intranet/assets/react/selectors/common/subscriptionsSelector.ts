import { createSelector } from "reselect";
import moment from "moment";
import { RootState } from "../../store";

const getSubscriptions = (state: RootState, iri: any) => {
  return state.common.subscriptions[iri];
};

export const getSubscriptionsMapping = createSelector(
  [getSubscriptions],
  (subscriptions) => {
    if (!subscriptions) {
      return null;
    }

    return Object.values(subscriptions)
      .map((subscription: any) => ({
        ...subscription,
        createdAt: moment(subscription.createdAt),
      }))
      .sort((subscription1, subscription2) => {
        return subscription2.createdAt - subscription1.createdAt;
      });
  }
);

const isSubscriptionCreationPending = (state: RootState, iri: any) => {
  return state.common.pendingCreations[iri];
};

export const isCreationPending = createSelector(
  [isSubscriptionCreationPending],
  (pending) => pending
);

const isSubscriptionDeletionPending = (state: RootState, iri: any) => {
  return state.common.pendingDeletions[iri];
};

export const isDeletionPending = createSelector(
  [isSubscriptionDeletionPending],
  (pending) => pending
);
