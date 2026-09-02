import { expect } from "chai";
import { describe, it } from "mocha";
import { getTags } from "../../actions/tagsActions";

describe("tagsActions", () => {
  describe("getTags", () => {
    const DEFAULT_URL = "/tags";
    const DISCRIMINATOR_URL = "/premises_tags";
    const API_ROUTE_PREFIX_URL = "/sales/tags";
    const COMBINED_URL = "/sales/sales_forecast_tags";

    it("should create an action to fetch tags with default parameters", () => {
      const expectedAction = {
        type: "TAG_FETCH_LIST",
        payload: {
          url: DEFAULT_URL,
        },
      };

      const action = getTags();

      expect(action).to.deep.equal(expectedAction);
    });

    it("should create an action to fetch tags with a discriminator", () => {
      const discriminator = "premises";
      const expectedAction = {
        type: "TAG_FETCH_LIST",
        payload: {
          url: DISCRIMINATOR_URL,
        },
      };

      const action = getTags(discriminator);

      expect(action).to.deep.equal(expectedAction);
    });

    it("should create an action to fetch tags with an apiRoutePrefix", () => {
      const apiRoutePrefix = "sales";
      const expectedAction = {
        type: "TAG_FETCH_LIST",
        payload: {
          url: API_ROUTE_PREFIX_URL,
        },
      };

      const action = getTags(null, apiRoutePrefix);

      expect(action).to.deep.equal(expectedAction);
    });

    it("should create an action to fetch tags with both discriminator and apiRoutePrefix", () => {
      const discriminator = "sales_forecast";
      const apiRoutePrefix = "sales";
      const expectedAction = {
        type: "TAG_FETCH_LIST",
        payload: {
          url: COMBINED_URL,
        },
      };

      const action = getTags(discriminator, apiRoutePrefix);

      expect(action).to.deep.equal(expectedAction);
    });
  });
});
