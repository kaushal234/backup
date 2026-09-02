import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/task/validation";

describe("mis project validation", () => {
  const defaultErrors = {
    shortDescription: Translator.trans("task.errors.short_description"),
    assignees: Translator.trans("task.errors.assignee"),
    description: Translator.trans("task.errors.description"),
    dueDate: Translator.trans("task.errors.due_date"),
    escalationTrigger: Translator.trans("task.errors.escalation_trigger"),
    indiceFactor: Translator.trans("task.errors.indices_factor"),
    module: Translator.trans("task.errors.module"),
    referenceId: Translator.trans("task.errors.reference_id"),
    startedAt: Translator.trans("task.errors.started_date"),
    unit: Translator.trans("task.errors.unit"),
  };
  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
});
