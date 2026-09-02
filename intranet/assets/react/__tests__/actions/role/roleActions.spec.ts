import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/role/roleActions";

describe("fetchRole", () => {
  describe("setRole", () => {
    it("should get an role to set search", () => {
      const fetchRole = {
        type: "ROLE_FETCH_ROLE",
        payload: {
          request: {
            url: `/groups?name=ROLE_GSE`,
          },
        },
      };
      expect(actions.fetchROLE("ROLE_GSE")).to.deep.equal(fetchRole);
    });
  });
});
