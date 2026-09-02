import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  if (!values.category) {
    errors.category = Translator.trans(
      "mis.specification.user_story_form.errors.category"
    );
  }
  if (!values.description) {
    errors["user-story"] = Translator.trans(
      "mis.specification.user_story_form.errors.user_story"
    );
  }
  return errors;
};
export default validate;
