import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/common/subscriptionsActions";

describe("subscriptionsActions", () => {
  describe("createSubscription", () => {
    it("should create a create action", () => {
      const expectedAction = {
        type: "COMMON_CREATE_SUBSCRIPTION",
        payload: {
          form: "my_form",
          request: {
            url: "/subscriptions?normalization_groups[]=people_photo&normalization_groups[]=file:light",
            body: {
              resource: "/resource/1",
              user: "/people/12",
            },
          },
        },
      };
      expect(
        actions.createSubscription("/resource/1", "/people/12", "my_form")
      ).to.deep.equal(expectedAction);
    });
  });
  describe("getSubscriptions", () => {
    it("should create a fetch subscriptions action", () => {
      const expectedAction = {
        type: "COMMON_FETCH_SUBSCRIPTIONS",
        payload: {
          url: `/subscriptions?resource=/resources/42&pagination=false&normalization_groups[]=people_photo&normalization_groups[]=file:light`,
          iri: "/resources/42",
        },
      };
      expect(actions.getSubscriptions("/resources/42")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("deleteSubscription", () => {
    it("should create a delete subscriptions action", () => {
      const expectedAction = {
        type: "COMMON_DELETE_SUBSCRIPTION",
        payload: {
          resourceIri: "/resources/42",
          subscriptionIri: "/subscriptions/69",
        },
      };
      expect(
        actions.deleteSubscription("/resources/42", "/subscriptions/69")
      ).to.deep.equal(expectedAction);
    });
  });
});
