import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/country/countriesActions";

describe("countriesActions", () => {
  describe("fetchCountry", () => {
    it("should create a fetch country action", () => {
      const expectedAction = {
        type: "API_FETCH_COUNTRY",
        payload: {
          request: {
            url: "/resource/1",
          },
        },
      };
      expect(actions.fetchCountry("/resource/1")).to.deep.equal(expectedAction);
    });
  });
  describe("fetchCountries", () => {
    it("should create a fetch countries action", () => {
      const expectedAction = {
        type: "API_FETCH_COUNTRIES",
        payload: {
          request: {
            url: "/countries?normalization_groups_override[]=country_list&order[name]=asc&q=FRA",
          },
        },
      };
      expect(actions.fetchCountries("FRA")).to.deep.equal(expectedAction);
    });
  });
});
