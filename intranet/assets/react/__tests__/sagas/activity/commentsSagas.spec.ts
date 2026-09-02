import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import {
  createComment,
  getComments,
} from "../../../actions/activity/commentsActions";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import {
  callCreateComment,
  callGetComments,
} from "../../../sagas/activity/commentsSagas";
import { client } from "../../../store";

describe("commentsSagas", () => {
  describe("callCreateComment", () => {
    describe("Successful calls", () => {
      const generator = callCreateComment(
        createComment("/resource/1", "my message", "image.png")
      );

      it("should first fetch the API", () => {
        const formData = new FormData();
        formData.append("resource", "/resource/1");
        formData.append("message", "my message");
        formData.append("file", "image.png");
        expect(generator.next({ data: formData }).value).to.deep.equal(
          call(client.post, "/comments", formData)
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("ACTIVITY_CREATE_COMMENT", {
              data: { id: 15 },
              iri: "/resource/1",
            })
          )
        );
      });
      it("should then dispatch a getComments event", () => {
        expect(generator.next().value).to.deep.equal(
          put(getComments("/resource/1"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateComment(
        createComment("/resource/1", "my message", null)
      );

      it("should first fetch the API", () => {
        const formData = new FormData();
        formData.append("resource", "/resource/1");
        formData.append("message", "my message");
        formData.append("file", "null");
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/comments", formData)
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("ACTIVITY_CREATE_COMMENT", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callGetComments", () => {
    describe("Successful calls", () => {
      const generator = callGetComments(getComments("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/comments?normalization_groups[]=file:light&normalizationGroups[]=activity_position&resource=/resource/2&pagination=false&extraComment=true"
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ iri: "/resource/2" }).value).to.deep.equal(
          put(APICallSuccess("ACTIVITY_FETCH_COMMENTS", { iri: "/resource/2" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetComments(getComments("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/comments?normalization_groups[]=file:light&normalizationGroups[]=activity_position&resource=/resource/2&pagination=false&extraComment=true"
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ iri: "/resource/2" }).value).to.deep.equal(
          put(APICallSuccess("ACTIVITY_FETCH_COMMENTS", { iri: "/resource/2" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetComments(getComments("/resource/2"));

      it("should first fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            client.get,
            "/comments?normalization_groups[]=file:light&normalizationGroups[]=activity_position&resource=/resource/2&pagination=false&extraComment=true"
          )
        );
      });
      const error = { response: { data: "test" } };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("ACTIVITY_FETCH_COMMENTS", { data: "test" }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
