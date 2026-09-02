import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/task/taskActions";
import { CREATE_TASK, EDIT_TASK } from "../../../constants";

describe("taskAction", () => {
  describe("createTask", () => {
    it("should create a fetch action", () => {
      const expectedAction = {
        type: "CREATE_TASK",
        payload: {
          form: "task_form",
          url: `/tasks`,
          body: { foo: "bar" },
        },
      };
      expect(actions.writeTask({ foo: "bar" }, "task_form")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("editTask", () => {
    const taskId = 1;
    it("should edit a fetch action", () => {
      const expectedAction = {
        type: "EDIT_TASK",
        payload: {
          form: "task_form",
          url: `/tasks/${taskId}`,
          body: { foo: "bar" },
        },
      };
      expect(
        actions.writeTask({ id: taskId, foo: "bar" }, "task_form")
      ).to.deep.equal(expectedAction);
    });
  });
});
