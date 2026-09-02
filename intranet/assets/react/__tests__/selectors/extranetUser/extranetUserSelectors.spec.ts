import { describe, it } from "mocha";
import { expect } from "chai";
import { getExtranetUsersMapping } from "../../../selectors/extranetUser/extranetUserSelectors";

const initialState: any = {
  extranetUser: {
    extranetUsers: [],
  },
};

describe("extranetUserSelector", () => {
  describe("getExtranetUsersMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        extranetUser: {
          extranetUsers: [
            {
              "@id": "/extranet_users/1",
              firstname: "Anto",
              lastname: "GRASSIOT",
              id: 666,
            },
            {
              "@id": "/extranet_users/2",
              firstname: "Philippe",
              lastname: "CARLE",
              id: 999,
            },
          ],
        },
      };
      expect(getExtranetUsersMapping(initialState)).to.deep.equal([]);
      expect(getExtranetUsersMapping.recomputations()).to.equal(1);
      expect(
        getExtranetUsersMapping(state).map((extranetUser) => {
          return { ...extranetUser };
        })
      ).to.deep.equal([
        { value: "/extranet_users/1", label: "GRASSIOT Anto (ID#666)" },
        { value: "/extranet_users/2", label: "CARLE Philippe (ID#999)" },
      ]);
      expect(getExtranetUsersMapping.recomputations()).to.equal(2);
      getExtranetUsersMapping(state);
      expect(getExtranetUsersMapping.recomputations()).to.equal(2);
    });
  });
});
