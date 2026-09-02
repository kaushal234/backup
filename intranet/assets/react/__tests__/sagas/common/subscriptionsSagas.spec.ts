import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill } from "redux-form";
import {
  createSubscription,
  deleteSubscription,
  getSubscriptions,
} from "../../../actions/common/subscriptionsActions";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import {
  callCreateSubscription,
  callDeleteSubscription,
  callGetSubscriptions,
} from "../../../sagas/common/subscriptionsSagas";
import { client } from "../../../store";

describe("subscriptionsSagas", () => {
  describe("callCreateSubscription", () => {
    describe("Successful calls", () => {
      const generator = callCreateSubscription(
        createSubscription("/resource/1", "/people/12", "my_form")
      );

      it("should first fetch the API", () => {
        expect(
          generator.next({
            data: { resource: "/resource/1", user: "/people/12" },
          }).value
        ).to.deep.equal(
          call(
            client.post,
            "/subscriptions?normalization_groups[]=people_photo&normalization_groups[]=file:light",
            { resource: "/resource/1", user: "/people/12" }
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("COMMON_CREATE_SUBSCRIPTION", { data: { id: 15 } })
          )
        );
      });
      it("should reset the user choice", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("my_form", `user`, null))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateSubscription(
        createSubscription("/resource/1", "/people/12")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.post,
            "/subscriptions?normalization_groups[]=people_photo&normalization_groups[]=file:light",
            { resource: "/resource/1", user: "/people/12" }
          )
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("COMMON_CREATE_SUBSCRIPTION", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callGetSubscriptions", () => {
    describe("Successful calls", () => {
      const generator = callGetSubscriptions(getSubscriptions("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/subscriptions?resource=/resource/2&pagination=false&normalization_groups[]=people_photo&normalization_groups[]=file:light"
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ iri: "/resource/2" }).value).to.deep.equal(
          put(
            APICallSuccess("COMMON_FETCH_SUBSCRIPTIONS", { iri: "/resource/2" })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetSubscriptions(getSubscriptions("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/subscriptions?resource=/resource/2&pagination=false&normalization_groups[]=people_photo&normalization_groups[]=file:light"
          )
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("COMMON_FETCH_SUBSCRIPTIONS", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callDeleteSubscriptions", () => {
    describe("Successful calls", () => {
      const generator = callDeleteSubscription(
        deleteSubscription("/resource/2", "/subscriptions/45")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.delete, "/subscriptions/45")
        );
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            resourceIri: "/resource/2",
            subscriptionIri: "/subscriptions/45",
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("COMMON_DELETE_SUBSCRIPTION", {
              resourceIri: "/resource/2",
              subscriptionIri: "/subscriptions/45",
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callDeleteSubscription(
        deleteSubscription("/resource/2", "/subscriptions/45")
      );

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.delete, "/subscriptions/45")
        );
      });
      const error = { response: {} };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("COMMON_DELETE_SUBSCRIPTION", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
