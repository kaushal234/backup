import { AnyAction, Reducer } from "redux";
import {
  COMMON_CREATE_SUBSCRIPTION,
  COMMON_DELETE_SUBSCRIPTION,
  COMMON_FETCH_SUBSCRIPTIONS,
  SUCCESS,
} from "../../constants";

interface ISubscriptionState {
  subscriptions: any;
  pendingCreations: any;
  pendingDeletions: any;
}

const initialState: ISubscriptionState = {
  subscriptions: {},
  pendingCreations: {},
  pendingDeletions: {},
};

const subscriptionsReducer: Reducer<ISubscriptionState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const subscriptions = { ...state.subscriptions };
  switch (action.type) {
    case COMMON_FETCH_SUBSCRIPTIONS + SUCCESS:
      return {
        ...state,
        subscriptions: {
          ...state.subscriptions,
          [action.payload.iri]: action.payload.data["hydra:member"],
        },
      };
    case COMMON_CREATE_SUBSCRIPTION:
      return {
        ...state,
        pendingCreations: {
          ...state.pendingCreations,
          [action.payload.request.body.resource]: true,
        },
      };
    case COMMON_CREATE_SUBSCRIPTION + SUCCESS: {
      const resourceIri = action.payload.data.resource;
      const newSubscriptions = Object.keys(
        subscriptions[resourceIri] || []
      ).map((id) => {
        return subscriptions[resourceIri][id];
      });

      newSubscriptions.push(action.payload.data);

      const pendingCreations = { ...state.pendingCreations };

      delete pendingCreations[resourceIri];

      return {
        ...state,
        pendingCreations,
        subscriptions: {
          ...state.subscriptions,
          [resourceIri]: newSubscriptions,
        },
      };
    }
    case COMMON_DELETE_SUBSCRIPTION:
      return {
        ...state,
        pendingDeletions: {
          ...state.pendingDeletions,
          [action.payload.subscriptionIri]: true,
        },
      };
    case COMMON_DELETE_SUBSCRIPTION + SUCCESS: {
      let subscriptionsCopy = Object.keys(
        subscriptions[action.payload.resourceIri] || []
      ).map((id) => {
        return subscriptions[action.payload.resourceIri][id];
      });

      subscriptionsCopy = subscriptionsCopy.filter((subscription) => {
        return subscription["@id"] !== action.payload.subscriptionIri;
      });

      const pendingDeletions = { ...state.pendingDeletions };

      delete pendingDeletions[action.payload.subscriptionIri];

      return {
        ...state,
        subscriptions: {
          ...state.subscriptions,
          [action.payload.resourceIri]: subscriptionsCopy,
        },
        pendingDeletions,
      };
    }
    default:
      break;
  }
  return state;
};

export default subscriptionsReducer;
