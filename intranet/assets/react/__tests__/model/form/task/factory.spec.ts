import { describe, it } from "mocha";
import { expect } from "chai";
import { taskFactory } from "../../../../model/form/task/factory";

const dummyTask = {
  id: null,
  shortDescription: "short description",
  description: "description",
  module: { value: "/modules/1" },
  referenceId: 1,
  escalationTrigger: 60,
  escalationTriggerUnit: "DAYS",
  indiceFactor: "IF 1",
  startedAt: "2026-01-01T05:00:00.000Z",
  dueDate: "2026-01-01T05:00:00.000Z",
  assignee: { value: "/peoples/2" },
  recipients: [{ value: "/peoples/3" }, { value: "/peoples/4" }],
  confidential: false,
};

describe("Task factory", () => {
  it("should return a task", () => {
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    const results = taskFactory(dummyTask);
    expect(results).to.deep.equal({
      id: null,
      shortDescription: "short description",
      description: "description",
      module: "/modules/1",
      referenceId: 1,
      escalationTrigger: 60,
      escalationTriggerUnit: "DAYS",
      indiceFactor: "IF 1",
      startedAt: "2026-01-01T00:00:00-05:00",
      dueDate: "2026-01-01T00:00:00-05:00",
      assignee: "/peoples/2",
      recipients: ["/peoples/3", "/peoples/4"],
      confidential: false,
    });
  });
});
