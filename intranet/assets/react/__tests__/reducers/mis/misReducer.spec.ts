import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/mis/misReducer";

const initialState = {
  types: [],
  modules: [],
  details: [],
  troubleTickets: [],
  tags: [],
  showSuccess: false,
  showError: false,
  showLoading: false,
  showFiles: false,
  isLoading: true,
  troubleTicketsByModule: {},
  troubleTicketsRefresh: false,
};

describe("misReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle MIS_FETCH_MODULES", () => {
    expect(reducer(initialState, { type: "MIS_FETCH_MODULES" })).to.deep.equal({
      ...initialState,
    });
  });
  it("should handle MIS_FETCH_MODULES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "MIS_FETCH_MODULES_SUCCESS",
        payload: {
          data: { "hydra:member": [{ "@id": "/modules/1", foo: "bar" }] },
        },
      })
    ).to.deep.equal({
      ...initialState,
      modules: { "/modules/1": { "@id": "/modules/1", foo: "bar" } },
    });
  });
  it("should handle MIS_FETCH_TAGS", () => {
    expect(reducer(initialState, { type: "MIS_FETCH_TAGS" })).to.deep.equal({
      ...initialState,
    });
  });
  it("should handle MIS_FETCH_TAGS_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "MIS_FETCH_TAGS_SUCCESS",
        payload: {
          data: { "hydra:member": [{ "@id": "/tags/1", foo: "bar" }] },
        },
      })
    ).to.deep.equal({
      ...initialState,
      tags: { "/tags/1": { "@id": "/tags/1", foo: "bar" } },
    });
  });
  it("should handle MIS_FETCH_TYPES", () => {
    expect(reducer(initialState, { type: "MIS_FETCH_TYPES" })).to.deep.equal({
      ...initialState,
    });
  });
  it("should handle MIS_FETCH_TYPES_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "MIS_FETCH_TYPES_SUCCESS",
        payload: {
          data: { "hydra:member": [{ "@id": "/types/1", foo: "bar" }] },
        },
      })
    ).to.deep.equal({
      ...initialState,
      types: { "/types/1": { "@id": "/types/1", foo: "bar" } },
    });
  });
  it("should handle MIS_FETCH_TROUBLE_TICKETS", () => {
    expect(
      reducer(initialState, { type: "MIS_FETCH_TROUBLE_TICKETS" })
    ).to.deep.equal({
      ...initialState,
    });
  });
  it("should handle MIS_UPDATE_TROUBLE_TICKET", () => {
    expect(
      reducer(initialState, { type: "MIS_UPDATE_TROUBLE_TICKET" })
    ).to.deep.equal({
      ...initialState,
      showLoading: true,
    });
  });
  it("should handle MIS_UPDATE_TROUBLE_TICKET_SUCCESS", () => {
    expect(
      reducer(initialState, { type: "MIS_UPDATE_TROUBLE_TICKET_SUCCESS" })
    ).to.deep.equal({
      ...initialState,
      showLoading: false,
      showSuccess: true,
    });
  });
  it("should handle MIS_UPDATE_TROUBLE_TICKET_FAILED", () => {
    expect(
      reducer(initialState, { type: "MIS_UPDATE_TROUBLE_TICKET_FAILED" })
    ).to.deep.equal({
      ...initialState,
      showError: true,
    });
  });
  it("should handle MIS_HIDE_SUCCESS_ALERT", () => {
    expect(
      reducer(initialState, { type: "MIS_HIDE_SUCCESS_ALERT" })
    ).to.deep.equal({
      ...initialState,
      showFiles: true,
    });
  });

  it("should handle  MIS_GET_TROUBLE_TICKETS_BY_MODULE_SUCCESS", () => {
    expect(
      reducer(
        { ...initialState },
        {
          type: "MIS_GET_TROUBLE_TICKETS_BY_MODULE_SUCCESS",
          payload: {
            data: {
              "hydra:member": { "@id": "/trouble_tickets/1", foo: "bar" },
            },
          },
        }
      )
    ).to.deep.equal({
      ...initialState,
      isLoading: false,
      troubleTicketsByModule: {
        "@id": "/trouble_tickets/1",
        foo: "bar",
      },
    });
  });
  it("should handle MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES", () => {
    expect(
      reducer(initialState, {
        type: "MIS_ADD_TROUBLE_TICKETS_TO_USER_STORIES_SUCCESS",
      })
    ).to.deep.equal({
      ...initialState,
      troubleTicketsRefresh: true,
    });
  });
  it("should handle MIS_TROUBLE_TICKETS_RESET", () => {
    expect(
      reducer(initialState, { type: "MIS_TROUBLE_TICKETS_RESET" })
    ).to.deep.equal({
      ...initialState,
      troubleTicketsRefresh: false,
    });
  });
});
