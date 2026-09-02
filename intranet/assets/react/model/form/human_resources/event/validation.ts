import moment from "moment";
import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.name) {
    errors.name = Translator.trans("events.validation.name");
  }

  if (!values.country) {
    errors.country = Translator.trans("events.validation.country");
  }

  if (!values.startedAt) {
    errors.startedAt = Translator.trans("events.validation.started_at");
  }

  if (values.startedAt && moment(values.startedAt) < moment()) {
    errors.startedAt = Translator.trans("events.validation.started_at_today");
  }

  if (!values.endedAt) {
    errors.endedAt = Translator.trans("events.validation.ended_at");
  }

  if (
    values.startedAt &&
    values.endedAt &&
    moment(values.startedAt) > moment(values.endedAt)
  ) {
    errors.endedAt = Translator.trans("events.validation.ended_at_before");
  }

  return errors;
};

export default validate;
