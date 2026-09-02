import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getUserMapping,
  getUsersListMapping,
} from "../../../selectors/user/userSelectors";

const initialState: any = {
  user: {
    details: {},
    users: [],
  },
};

describe("userSelectors", () => {
  describe("getUsersListMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        user: {
          users: [
            {
              "@id": "/people/1",
              firstname: "Anto",
              lastname: "GRASSIOT",
              email: "foo@bar.fr",
            },
            {
              "@id": "/people/2",
              firstname: "Philippe",
              lastname: "CARLE",
              email: "bar@bar.fr",
            },
          ],
        },
      };
      expect(getUsersListMapping(initialState)).to.deep.equal([]);
      expect(getUsersListMapping.recomputations()).to.equal(1);
      expect(getUsersListMapping(state)).to.deep.equal([
        { value: "/people/1", label: "GRASSIOT Anto - foo@bar.fr" },
        { value: "/people/2", label: "CARLE Philippe - bar@bar.fr" },
      ]);
      expect(getUsersListMapping.recomputations()).to.equal(2);
      getUsersListMapping(state);
      expect(getUsersListMapping.recomputations()).to.equal(2);
    });
    it("should handle custom path", () => {
      const state: any = {
        user: {
          users: [
            {
              "@id": "/people/1",
              firstname: "Anto",
              lastname: "GRASSIOT",
              email: "foo@bar.fr",
            },
            {
              "@id": "/people/2",
              firstname: "Philippe",
              lastname: "CARLE",
              email: "bar@bar.fr",
            },
          ],
          qam: [
            {
              "@id": "/people/12",
              firstname: "Lami",
              lastname: "GRASSIOT",
              email: "barfoo@bar.fr",
            },
            {
              "@id": "/people/21",
              firstname: "Sacha",
              lastname: "Dubourgpalette",
              email: "foorbar@bar.fr",
            },
          ],
        },
      };
      getUsersListMapping.resetRecomputations();
      expect(getUsersListMapping(initialState)).to.deep.equal([]);
      expect(getUsersListMapping.recomputations()).to.equal(1);
      expect(getUsersListMapping(state, "qam")).to.deep.equal([
        { value: "/people/12", label: "GRASSIOT Lami - barfoo@bar.fr" },
        { value: "/people/21", label: "Dubourgpalette Sacha - foorbar@bar.fr" },
      ]);
      expect(getUsersListMapping.recomputations()).to.equal(2);
      getUsersListMapping(state, "qam");
      expect(getUsersListMapping.recomputations()).to.equal(2);
    });
  });
  describe("getUserMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        user: {
          users: [
            {
              "@id": "/people/1",
              firstname: "Anto",
              lastname: "GRASSIOT",
              email: "foo@bar.fr",
            },
            {
              "@id": "/people/2",
              firstname: "Philippe",
              lastname: "CARLE",
              email: "bar@bar.fr",
            },
          ],
        },
        iri: "/people/2",
      };
      expect(getUserMapping(initialState)).to.deep.equal(null);
      expect(getUserMapping.recomputations()).to.equal(1);
      expect(getUserMapping(state)).to.deep.equal({
        value: "/people/2",
        label: "CARLE Philippe - bar@bar.fr",
      });
      expect(getUserMapping.recomputations()).to.equal(2);
      getUserMapping(state);
      expect(getUserMapping.recomputations()).to.equal(2);
    });
  });
});
