import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/user_story/validation";

describe("user story validation", () => {
  const defaultErrors = {
    category: Translator.trans(
      "mis.specification.user_story_form.errors.category"
    ),
    "user-story": Translator.trans(
      "mis.specification.user_story_form.errors.user_story"
    ),
  };

  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
});
