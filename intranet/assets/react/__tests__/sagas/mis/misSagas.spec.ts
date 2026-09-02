import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { fetchAPI } from "../../../utils/api";
import {
  callAddTroubleTicketToUserStories,
  callGetTroubleTicketsByModule,
  callType,
  callUpdateTroubleTicket,
} from "../../../sagas/mis/misSagas";
import { fetchType } from "../../../actions/mis/typesActions";
import {
  addTroubleTicketToUserStories,
  getTroubleTicketsByModule,
  updateTroubleTicket,
} from "../../../actions/mis/troubleTicketsActions";

describe("misSagas", () => {
  describe("callType", () => {
    describe("Successful calls", () => {
      const generator = callType(fetchType(1));
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/mis/types/1")
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { id: 1, indiceFactor: "IF 1" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("MIS_FETCH_TYPE", {
              data: { id: 1, indiceFactor: "IF 1" },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("trouble_ticket_form", "indiceFactor", "IF 1"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callType(fetchType(1));
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(fetchAPI, "/mis/types/1")
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("MIS_FETCH_TYPE", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callUpdateTroubleTicket", () => {
    describe("Successful calls", () => {
      const generator = callUpdateTroubleTicket(
        updateTroubleTicket({ id: 1, foo: "bar" })
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", { id: 1, foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { id: 1, foo: "bar" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("MIS_UPDATE_TROUBLE_TICKET", {
              data: { id: 1, foo: "bar" },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateTroubleTicket(
        updateTroubleTicket({ id: 1, foo: "bar" })
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", { id: 1, foo: "bar" })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("MIS_UPDATE_TROUBLE_TICKET", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callUpdateTroubleTicket", () => {
    describe("Successful calls", () => {
      const generator = callUpdateTroubleTicket(
        updateTroubleTicket(
          { id: 1, foo: "bar" },
          "MIS_UPDATE_TROUBLE_TICKET_OWNERS"
        )
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", { id: 1, foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { id: 1, foo: "bar" } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("MIS_UPDATE_TROUBLE_TICKET_OWNERS", {
              data: { id: 1, foo: "bar" },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callUpdateTroubleTicket(
        updateTroubleTicket(
          { id: 1, foo: "bar" },
          "MIS_UPDATE_TROUBLE_TICKET_OWNERS"
        )
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", { id: 1, foo: "bar" })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("MIS_UPDATE_TROUBLE_TICKET_OWNERS", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callGetTroubleTicketsByModule", () => {
    describe("Successful calls", () => {
      const generator = callGetTroubleTicketsByModule(
        getTroubleTicketsByModule(1)
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/mis/trouble_tickets?module=/modules/1&status[0]=AWAITING%20USER&status[1]=IN%20PROGRESS&status[3]=SOLUTION%20PROPOSED&status[4]=PENDING&status[5]=MOO/GKU%20AWAITING%20USER&status[6]=PENDING%20MOO/GKU&type.type=Request"
          )
        );
      });
      it("should then dispatch a success event", () => {
        const responseData = {
          data: {
            "@id":
              "/mis/trouble_tickets?module=/modules/1&status[0]=AWAITING%20USER&status[1]=IN%20PROGRESS&status[3]=SOLUTION%20PROPOSED&status[4]=PENDING",
          },
        };
        expect(generator.next(responseData).value).to.deep.equal(
          put(APICallSuccess("MIS_GET_TROUBLE_TICKETS_BY_MODULE", responseData))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetTroubleTicketsByModule(
        getTroubleTicketsByModule(1)
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/mis/trouble_tickets?module=/modules/1&status[0]=AWAITING%20USER&status[1]=IN%20PROGRESS&status[3]=SOLUTION%20PROPOSED&status[4]=PENDING&status[5]=MOO/GKU%20AWAITING%20USER&status[6]=PENDING%20MOO/GKU&type.type=Request"
          )
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                shouldIri: "pathLeChien",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("MIS_GET_TROUBLE_TICKETS_BY_MODULE", error.response)
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callAddTroubleTicketToUserStories", () => {
    describe("Successful calls", () => {
      const generator = callAddTroubleTicketToUserStories(
        addTroubleTicketToUserStories(1)
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", {
            isAddToUserStories: true,
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({ data: { isAddToUserStories: true } }).value
        ).to.deep.equal(
          put(
            APICallSuccess("MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES", {
              data: { isAddToUserStories: true },
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });

    describe("Failing calls", () => {
      const generator = callAddTroubleTicketToUserStories(
        addTroubleTicketToUserStories(1)
      );
      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/trouble_tickets/1", {
            isAddToUserStories: true,
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            violations: [
              {
                propertyPath: "pathLeChien",
                message: "in a bottle",
              },
            ],
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed(
              "MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES",
              error.response
            )
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
