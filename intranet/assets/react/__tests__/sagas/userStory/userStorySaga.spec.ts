import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import {
  callGetUserStory,
  callWriteUserStory,
  callEditUserStory,
  callUpdateStatusUserStory,
  callDeleteUserStory,
  callGetSpecification,
} from "../../../sagas/userStory/userStorySagas";
import {
  deleteUserStory,
  getSpecification,
  getUserStory,
  updateStatusUserStory,
  writeUserStory,
} from "../../../actions/userStory/userStoryRequestAction";
import { USER_STORY_EDIT } from "../../../constants";

describe("userStorySaga", () => {
  describe("callWriteUserStory", () => {
    describe("Successful calls", () => {
      const generator = callWriteUserStory(
        writeUserStory({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/mis/user_stories", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 5 } }).value).to.deep.equal(
          put(APICallSuccess("USER_STORY_WRITE", { data: { id: 5 } }))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "id", 5))
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form_name" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
    });
    describe("Failing calls", () => {
      const generator = callWriteUserStory(
        writeUserStory({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/mis/user_stories", { foo: "bar" })
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
          put(APICallFailed("USER_STORY_WRITE", error.response))
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then stop form submission", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callEditUserStory", () => {
    describe("Successful calls", () => {
      const generator = callEditUserStory(
        writeUserStory({ foo: "bar", id: 5 }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/user_stories/5", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 5 } }).value).to.deep.equal(
          put(APICallSuccess("USER_STORY_EDIT", { data: { id: 5 } }))
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form_name" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callEditUserStory(
        writeUserStory({ foo: "bar", id: 5 }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/mis/user_stories/5", { foo: "bar" })
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
          put(APICallFailed(USER_STORY_EDIT, error.response))
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then stop form submission", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });

  describe("callGetSpecification", () => {
    describe("Successful calls", () => {
      const generator = callGetSpecification(getSpecification(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/mis/specifications/1")
        );
      });
      it("should then dispatch a success event", () => {
        const responseData = { data: { foo: "bar" } };
        expect(generator.next(responseData).value).to.deep.equal(
          put(APICallSuccess("SPECIFICATION_GET", responseData))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetSpecification(getSpecification(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/mis/specifications/1")
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
          put(APICallFailed("SPECIFICATION_GET", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });

  describe("callGetUserStory", () => {
    describe("Successful calls", () => {
      const generator = callGetUserStory(getUserStory(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/mis/user_stories/1")
        );
      });
      it("should then dispatch a success event", () => {
        const responseData = { data: { "@id": "mis/user_stories/1" } };
        expect(generator.next(responseData).value).to.deep.equal(
          put(APICallSuccess("USER_STORY_GET", responseData))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetUserStory(getUserStory(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/mis/user_stories/1")
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
          put(APICallFailed("USER_STORY_GET", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });

  describe("callDeleteUserStory", () => {
    describe("Successful calls", () => {
      const generator = callDeleteUserStory(deleteUserStory(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.delete, "/mis/user_stories/1")
        );
      });
      it("should then dispatch a success event", () => {
        const responseData = "mis/user_stories/1";
        expect(generator.next(responseData).value).to.deep.equal(
          put(
            APICallSuccess("USER_STORY_DELETE", {
              response: "mis/user_stories/1",
              url: "/mis/user_stories/1",
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callGetUserStory(deleteUserStory(1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.get, "/mis/user_stories/1")
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
          put(APICallFailed("USER_STORY_DELETE", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });

  describe("callUpdateStatusUserStory", () => {
    describe("Successful calls", () => {
      const generator = callUpdateStatusUserStory(
        updateStatusUserStory(1, "PLANNED")
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "mis/user_stories/1/status", {
            status: "DEVELOPMENT",
          })
        );
      });
      it("should then dispatch a success event", () => {
        const responseData = { data: { status: "PLANNED" } };
        expect(generator.next(responseData).value).to.deep.equal(
          put(APICallSuccess("USER_STORY_UPDATE_STATUS", responseData))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callUpdateStatusUserStory(
        updateStatusUserStory("1", "PLANNED")
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "mis/user_stories/1/status", {
            status: "DEVELOPMENT",
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            shouldBeStatus: "pathLeChien",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("USER_STORY_UPDATE_STATUS", error.response))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
