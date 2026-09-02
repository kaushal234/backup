import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/mis/troubleTicketsActions";

describe("troubleTicketsAction", () => {
  describe("updateTroubleTicket", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "MIS_UPDATE_TROUBLE_TICKET",
        payload: {
          request: {
            url: `/mis/trouble_tickets/1`,
            body: { id: 1 },
          },
        },
      };
      expect(actions.updateTroubleTicket({ id: 1 })).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("getTroubleTicketsByModule", () => {
    it("should send the correct type and payload to the saga", () => {
      const moduleId = 1;
      const expectedAction = {
        type: "MIS_GET_TROUBLE_TICKETS_BY_MODULE",
        payload: {
          url: `/mis/trouble_tickets?module=/modules/${moduleId}&status[0]=AWAITING%20USER&status[1]=IN%20PROGRESS&status[3]=SOLUTION%20PROPOSED&status[4]=PENDING&status[5]=MOO/GKU%20AWAITING%20USER&status[6]=PENDING%20MOO/GKU&type.type=Request`,
        },
      };
      expect(actions.getTroubleTicketsByModule(moduleId)).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("addTroubleTicketToUserStories", () => {
    it("should send the correct type and payload to the saga", () => {
      const troubleTicketId = 1;
      const expectedAction = {
        type: "MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES",
        payload: {
          url: `/mis/trouble_tickets/${troubleTicketId}`,
          body: {
            isAddToUserStories: true,
          },
        },
      };
      expect(
        actions.addTroubleTicketToUserStories(troubleTicketId)
      ).to.deep.equal(expectedAction);
    });
  });
  describe("getTroubleTicketsReset", () => {
    it("should send the correct type the reducer", () => {
      const expectedAction = {
        type: "MIS_TROUBLE_TICKETS_RESET",
      };
      expect(actions.getTroubleTicketsReset()).to.deep.equal(expectedAction);
    });
  });
});
