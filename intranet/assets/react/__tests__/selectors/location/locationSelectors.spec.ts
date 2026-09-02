import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getLocationsMapping,
  getLocationMapping,
  getUserDefaultLocationMapping,
} from "../../../selectors/location/locationSelectors";
import { getFactoriesMapping } from "../../../selectors/location/factoriesSelector";
import { RootState } from "../../../store";

const initialState: any = {
  location: {
    locations: [],
    factories: [],
  },
  user: {},
};

describe("locationSelectors", () => {
  describe("getLocationsMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        location: {
          locations: [
            {
              "@id": "/foo/1",
              name: "Foo",
              erp: 42,
              currency: { name: "EUR" },
            },
            {
              "@id": "/foo/2",
              name: "Bar",
              erp: 1984,
              currency: { name: "USD" },
            },
          ],
        },
      };
      expect(getLocationsMapping(initialState)).to.deep.equal([]);
      expect(getLocationsMapping.recomputations()).to.equal(1);
      expect(getLocationsMapping(state)).to.deep.equal([
        { value: "/foo/1", label: "Foo", erp: 42, currency: "EUR" },
        { value: "/foo/2", label: "Bar", erp: 1984, currency: "USD" },
      ]);
      expect(getLocationsMapping.recomputations()).to.equal(2);
      getLocationsMapping(state);
      expect(getLocationsMapping.recomputations()).to.equal(2);
    });
  });
  describe("getLocationMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        location: {
          locations: [
            {
              "@id": "/foo/1",
              name: "Foo",
              erp: 42,
              currency: { name: "EUR" },
            },
            {
              "@id": "/foo/2",
              name: "Bar",
              erp: 1984,
              currency: { name: "USD" },
            },
          ],
        },
        erp: 1984,
      };
      expect(getLocationMapping(initialState)).to.deep.equal(null);
      expect(getLocationMapping.recomputations()).to.equal(1);
      expect(getLocationMapping(state)).to.deep.equal({
        value: "/foo/2",
        label: "Bar",
        erp: 1984,
        currency: "USD",
      });
      expect(getLocationMapping.recomputations()).to.equal(2);
      getLocationMapping(state);
      expect(getLocationMapping.recomputations()).to.equal(2);
    });
  });
  describe("getUserDefaultLocationMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        location: {
          locations: [
            {
              "@id": "/foo/1",
              name: "Foo",
              erp: 42,
              currency: { name: "EUR" },
            },
            {
              "@id": "/foo/2",
              name: "Bar",
              erp: 1984,
              currency: { name: "USD" },
            },
            {
              "@id": "/foo/7",
              name: "TLD",
              erp: 500,
              currency: { name: "USD" },
            },
          ],
        },
        erp: 1984,
        user: {
          details: { erp: 500 },
        },
      };
      expect(getUserDefaultLocationMapping(initialState)).to.deep.equal(null);
      expect(getUserDefaultLocationMapping.recomputations()).to.equal(1);
      expect(getUserDefaultLocationMapping(state)).to.deep.equal({
        value: "/foo/7",
        label: "TLD",
        erp: 500,
        currency: "USD",
      });
      expect(getUserDefaultLocationMapping.recomputations()).to.equal(2);
      getUserDefaultLocationMapping(state);
      expect(getUserDefaultLocationMapping.recomputations()).to.equal(2);
    });
  });
  describe("getFactoriesMapping", () => {
    it("should use memoization", () => {
      const state = {
        location: {
          factories: [
            {
              erp: 42,
              name: "Foo",
              "@id": "/foo/1",
            },
            {
              erp: 1984,
              name: "Bar",
              "@id": "/foo/2",
            },
            {
              erp: 500,
              name: "TLD",
              "@id": "/foo/7",
            },
          ],
        },
      } as RootState;
      expect(getFactoriesMapping(initialState)).to.deep.equal([]);
      expect(getFactoriesMapping.recomputations()).to.equal(1);
      expect(getFactoriesMapping(state)).to.deep.equal([
        {
          erp: 42,
          label: "Foo - 42",
          value: "/foo/1",
        },
        {
          erp: 1984,
          value: "/foo/2",
          label: "Bar - 1984",
        },
        {
          erp: 500,
          value: "/foo/7",
          label: "TLD - 500",
        },
      ]);
      expect(getFactoriesMapping.recomputations()).to.equal(2);
      getFactoriesMapping(state);
      expect(getFactoriesMapping.recomputations()).to.equal(2);
    });
  });
});
