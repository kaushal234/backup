import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.shortDescription) {
    errors.shortDescription = Translator.trans("task.errors.short_description");
  }

  if (!values.description) {
    errors.description = Translator.trans("task.errors.description");
  }

  if (!values.module) {
    errors.module = Translator.trans("task.errors.module");
  }

  if (!values.referenceId) {
    errors.referenceId = Translator.trans("task.errors.reference_id");
  }

  if (!values.escalationTrigger) {
    errors.escalationTrigger = Translator.trans(
      "task.errors.escalation_trigger"
    );
  }

  if (!values.unit) {
    errors.unit = Translator.trans("task.errors.unit");
  }

  if (!values.startedAt) {
    errors.startedAt = Translator.trans("task.errors.started_date");
  }

  if (
    !values.id &&
    values.startedAt &&
    values.startedAt.getTime() < new Date().setHours(0, 0, 0, 0)
  ) {
    errors.startedAt = Translator.trans("task.errors.started_date_in_past");
  }

  if (!values.dueDate) {
    errors.dueDate = Translator.trans("task.errors.due_date");
  }

  if (
    !values.id &&
    values.dueDate &&
    values.dueDate.getTime() < new Date().setHours(0, 0, 0, 0)
  ) {
    errors.dueDate = Translator.trans("task.errors.due_date_in_past");
  }

  if (!values.indiceFactor) {
    errors.indiceFactor = Translator.trans("task.errors.indices_factor");
  }

  if (!values.assignees) {
    errors.assignees = Translator.trans("task.errors.assignee");
  }

  return errors;
};

export default validate;
