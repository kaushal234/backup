import { describe, it } from "mocha";
import { expect } from "chai";
import { getSubscriptionsMapping } from "../../../selectors/common/subscriptionsSelector";

const initialState: any = {
  common: {
    subscriptions: [],
  },
};

describe("subscriptionsSelector", () => {
  describe("getSubscriptionsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        common: {
          subscriptions: {
            "/foo/1": [
              { "@id": "/subscriptions/1", createdAt: "2019-01-01" },
              { "@id": "/subscriptions/2", createdAt: "2018-01-01" },
            ],
          },
        },
      };
      expect(getSubscriptionsMapping(initialState, "")).to.deep.equal(null);
      expect(getSubscriptionsMapping.recomputations()).to.equal(1);
      expect(
        getSubscriptionsMapping(state, "/foo/1")?.map((subscription) => {
          return { ...subscription, createdAt: null };
        })
      ).to.deep.equal([
        { "@id": "/subscriptions/1", createdAt: null },
        { "@id": "/subscriptions/2", createdAt: null },
      ]);
      expect(getSubscriptionsMapping.recomputations()).to.equal(2);
      getSubscriptionsMapping(state, "/foo/1");
      expect(getSubscriptionsMapping.recomputations()).to.equal(2);
    });
  });
});
