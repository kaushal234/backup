import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getSalesForecastsCommentsMapping,
  getSalesForecastsMapping,
} from "../../../selectors/sfr/salesForecastSelector";

const initialState: any = {
  sfr: {
    salesForecasts: [],
    comments: [],
  },
};

describe("salesForecastSelector", () => {
  describe("getSalesForecastsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        sfr: {
          salesForecasts: {
            "/foo/1": { "@id": "/foo/1", name: "Foo" },
            "/foo/2": { "@id": "/foo/2", name: "Bar" },
          },
        },
      };
      expect(getSalesForecastsMapping(initialState)).to.deep.equal([]);
      expect(getSalesForecastsMapping.recomputations()).to.equal(1);
      expect(
        getSalesForecastsMapping(state).map((salesForecast) => {
          return { ...salesForecast, estimatedSaleDate: null };
        })
      ).to.deep.equal([
        { "@id": "/foo/1", name: "Foo", estimatedSaleDate: null, index: 0 },
        { "@id": "/foo/2", name: "Bar", estimatedSaleDate: null, index: 1 },
      ]);
      expect(getSalesForecastsMapping.recomputations()).to.equal(2);
      getSalesForecastsMapping(state);
      expect(getSalesForecastsMapping.recomputations()).to.equal(2);
    });
  });
  describe("getSalesForecastsCommentsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        sfr: {
          comments: {
            4: [{ "@id": "/foo/1", name: "Foo" }],
            12: [{ "@id": "/foo/2", name: "Bar" }],
          },
        },
      };
      expect(getSalesForecastsCommentsMapping(initialState, 0)).to.deep.equal(
        null
      );
      expect(getSalesForecastsCommentsMapping.recomputations()).to.equal(1);
      expect(
        getSalesForecastsCommentsMapping(state, 4)?.map((comment) => {
          return { ...comment, createdAt: null };
        })
      ).to.deep.equal([
        { "@id": "/foo/1", name: "Foo", createdAt: null, index: 0 },
      ]);
      expect(getSalesForecastsCommentsMapping.recomputations()).to.equal(2);
      getSalesForecastsCommentsMapping(state, 4);
      expect(getSalesForecastsCommentsMapping.recomputations()).to.equal(2);
    });
  });
});
