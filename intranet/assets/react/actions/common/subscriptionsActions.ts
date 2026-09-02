import {
  COMMON_CREATE_SUBSCRIPTION,
  COMMON_DELETE_SUBSCRIPTION,
  COMMON_FETCH_SUBSCRIPTIONS,
} from "../../constants";

export function getSubscriptions(iri: any) {
  return {
    type: COMMON_FETCH_SUBSCRIPTIONS,
    payload: {
      url: `/subscriptions?resource=${iri}&pagination=false&normalization_groups[]=people_photo&normalization_groups[]=file:light`,
      iri,
    },
  };
}

export function createSubscription(resource: any, user: any, form: any = null) {
  return {
    type: COMMON_CREATE_SUBSCRIPTION,
    payload: {
      form,
      request: {
        url: "/subscriptions?normalization_groups[]=people_photo&normalization_groups[]=file:light",
        body: {
          resource,
          user,
        },
      },
    },
  };
}

export function deleteSubscription(resourceIri: any, subscriptionIri: any) {
  return {
    type: COMMON_DELETE_SUBSCRIPTION,
    payload: {
      resourceIri,
      subscriptionIri,
    },
  };
}
