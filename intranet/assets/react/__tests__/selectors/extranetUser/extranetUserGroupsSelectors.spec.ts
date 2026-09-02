import { describe, it } from "mocha";
import { expect } from "chai";
import { getExtranetUserGroupsMapping } from "../../../selectors/extranetUser/extranetUserGroups";

const initialState: any = {
  extranetUser: {
    extranetUsergroups: [],
  },
};

describe("extranetUserGroupsSelector", () => {
  describe("getExtranetUserGroupsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        extranetUser: {
          extranetUserGroups: [
            {
              "@id": "/extranet_user_groups/1",
              name: "eParts",
              description: "Groups for eParts",
            },
            {
              "@id": "/extranet_user_groups/2",
              name: "BoyzBand",
              description: "Group for Boyz Bands",
            },
          ],
        },
      };
      expect(getExtranetUserGroupsMapping(initialState)).to.deep.equal([]);
      expect(getExtranetUserGroupsMapping.recomputations()).to.equal(1);
      expect(
        getExtranetUserGroupsMapping(state).map((extranetUserGroup: any) => {
          return { ...extranetUserGroup };
        })
      ).to.deep.equal([
        {
          value: "/extranet_user_groups/1",
          label: "eParts: Groups for eParts",
        },
        {
          value: "/extranet_user_groups/2",
          label: "BoyzBand: Group for Boyz Bands",
        },
      ]);
      expect(getExtranetUserGroupsMapping.recomputations()).to.equal(2);
      getExtranetUserGroupsMapping(state);
      expect(getExtranetUserGroupsMapping.recomputations()).to.equal(2);
    });
  });
});
