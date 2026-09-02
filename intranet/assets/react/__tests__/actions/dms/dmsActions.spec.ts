import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/dms/dmsActions";

describe("dmsActions", () => {
  describe("fetchDms", () => {
    it("should create a fetch dms action", () => {
      const expectedAction = {
        type: "DMS_FETCH_DMS",
        payload: {
          request: {
            url: "/dms?normalization_groups_override[]=document_list&normalization_groups_override[]=expose_legacy&q=1524",
          },
        },
      };
      expect(actions.fetchDMS("1524")).to.deep.equal(expectedAction);
    });
  });
});
