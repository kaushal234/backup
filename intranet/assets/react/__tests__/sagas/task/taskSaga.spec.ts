import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { generateFormErrors } from "../../../utils/api";
import { writeTask } from "../../../actions/task/taskActions";
import { callCreateTask, callEditTask } from "../../../sagas/task/taskSagas";
import { callEditUserStory } from "../../../sagas/userStory/userStorySagas";
import { CREATE_TASK, EDIT_TASK } from "../../../constants";

describe("taskSagas", () => {
  describe("callCreateTask", () => {
    describe("Successful calls", () => {
      const generator = callCreateTask(writeTask({ foo: "bar" }, "form"));
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/tasks", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 5 } }).value).to.deep.equal(
          put(APICallSuccess(CREATE_TASK, { data: { id: 5 } }))
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
      const generator = callCreateTask(writeTask({ foo: "bar" }, "form"));
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/tasks", { foo: "bar" })
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
          put(APICallFailed(CREATE_TASK, error.response))
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
  describe("callEditTask", () => {
    describe("Successful calls", () => {
      const generator = callEditTask(writeTask({ foo: "bar", id: 5 }, "form"));
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/tasks/5", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 5 } }).value).to.deep.equal(
          put(APICallSuccess("EDIT_TASK", { data: { id: 5 } }))
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
        writeTask({ foo: "bar", id: 5 }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/tasks/5", { foo: "bar" })
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
          put(APICallFailed(EDIT_TASK, error.response))
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
});
