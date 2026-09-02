import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/part/partActions";

describe("partActions", () => {
  describe("writePartObject", () => {
    it("should create a create spare parts request action", () => {
      const expectedAction = {
        type: "PART_EDIT_PART_OBJECT",
        form: "foo_form",
        payload: {
          url: "/foo/1",
          body: { "@id": "/foo/1", foo: "bar" },
        },
      };
      expect(
        actions.writePartObject({ "@id": "/foo/1", foo: "bar" }, "foo_form")
      ).to.deep.equal(expectedAction);
    });
  });
});
