import { EVENT_ADD_EVENT, EVENT_UPDATE_EVENT } from "../../../constants";

export function addEvent(payload: any) {
  return {
    type: EVENT_ADD_EVENT,
    payload: {
      request: {
        url: "/events",
        body: payload,
      },
    },
  };
}

export function updateEvent(payload: any) {
  return {
    type: EVENT_UPDATE_EVENT,
    payload: {
      request: {
        url: `/events/${payload.id}`,
        body: payload,
      },
    },
  };
}
