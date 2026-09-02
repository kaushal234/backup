import {
  MIS_UPDATE_TROUBLE_TICKET,
  MIS_GET_TROUBLE_TICKETS_BY_MODULE,
  MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES,
  MIS_TROUBLE_TICKETS_RESET,
} from "../../constants";

export function updateTroubleTicket(
  troubleTicket: any,
  type = MIS_UPDATE_TROUBLE_TICKET
) {
  return {
    type,
    payload: {
      request: {
        url: `/mis/trouble_tickets/${troubleTicket.id}`,
        body: troubleTicket,
      },
    },
  };
}
export function getTroubleTicketsByModule(moduleId: any) {
  return {
    type: MIS_GET_TROUBLE_TICKETS_BY_MODULE,
    payload: {
      url: `/mis/trouble_tickets?module=/modules/${moduleId}&status[0]=AWAITING%20USER&status[1]=IN%20PROGRESS&status[3]=SOLUTION%20PROPOSED&status[4]=PENDING&status[5]=MOO/GKU%20AWAITING%20USER&status[6]=PENDING%20MOO/GKU&type.type=Request`,
    },
  };
}

export function addTroubleTicketToUserStories(troubleTicketId: any) {
  return {
    type: MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES,
    payload: {
      url: `/mis/trouble_tickets/${troubleTicketId}`,
      body: {
        isAddToUserStories: true,
      },
    },
  };
}

export function getTroubleTicketsReset() {
  return {
    type: MIS_TROUBLE_TICKETS_RESET,
  };
}
