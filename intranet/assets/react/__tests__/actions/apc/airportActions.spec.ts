import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/apc/airportsActions";

describe("airportsActions", () => {
  describe("fetchAirport", () => {
    it("should create a fetch airport action", () => {
      const expectedAction = {
        type: "APC_FETCH_AIRPORT",
        form: "form",
        payload: {
          request: {
            url: "/resource/1",
          },
        },
      };
      expect(actions.fetchAirport("/resource/1", "form")).to.deep.equal(
        expectedAction
      );
    });
  });
  describe("fetchAirports", () => {
    it("should create a fetch airports action", () => {
      const expectedAction = {
        type: "APC_FETCH_AIRPORTS",
        payload: {
          request: {
            url: "/airports?normalization_groups_override[]=airport_list&order[code]=asc&q=FFS",
          },
        },
      };
      expect(actions.fetchAirports("FFS")).to.deep.equal(expectedAction);
    });
  });
});
