import { describe, it } from "mocha";
import { expect } from "chai";
import {
  getCustomersMapping,
  getCustomerDeliveryAddress,
  getCustomerDeliveryAddressesMapping,
  getBusinessPartnersMapping,
  getPartsListMapping,
} from "../../../selectors/erp/erpSelectors";

const initialState: any = {
  erp: {
    customers: [],
    customer: {},
    loaders: { customer: false },
    units: [],
    parts: [],
  },
  user: {},
};

describe("erpSelectors", () => {
  describe("getPartsListMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        erp: {
          parts: [
            { "@id": "/foo/1", item: "123456", itemDescription: "description" },
          ],
        },
      };
      expect(getPartsListMapping(initialState)).to.deep.equal([]);
      expect(getPartsListMapping.recomputations()).to.equal(1);
      expect(getPartsListMapping(state)).to.deep.equal([
        {
          value: "123456",
          label: "123456 - description",
          "@id": "/foo/1",
          itemDescription: "description",
          item: "123456",
        },
      ]);
      expect(getPartsListMapping.recomputations()).to.equal(2);
      getPartsListMapping(state);
      expect(getPartsListMapping.recomputations()).to.equal(2);
    });
  });
  describe("getCustomersMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        erp: {
          customers: [
            { cuno: "AF1000", name: "Foo" },
            { cuno: "AF1001", name: "Bar" },
          ],
        },
      };
      expect(getCustomersMapping(initialState)).to.deep.equal([]);
      expect(getCustomersMapping.recomputations()).to.equal(1);
      expect(getCustomersMapping(state)).to.deep.equal([
        { value: "AF1000", name: "Foo", label: "AF1000 - Foo" },
        { value: "AF1001", name: "Bar", label: "AF1001 - Bar" },
      ]);
      expect(getCustomersMapping.recomputations()).to.equal(2);
      getCustomersMapping(state);
      expect(getCustomersMapping.recomputations()).to.equal(2);
    });
  });
  describe("getCustomerDeliveryAddress", () => {
    it("should return the delivery address detail", () => {
      const state: any = {
        erp: {
          customer: {
            deliveryAddresses: [
              { cdel: "023", name: "Foo" },
              { cdel: "023", name: "Bar" },
              { cdel: "025", name: "Baz" },
            ],
          },
        },
      };
      expect(getCustomerDeliveryAddress(initialState, "")).to.deep.equal([]);
      expect(getCustomerDeliveryAddress(state, "023")).to.deep.equal({
        cdel: "023",
        name: "Foo",
      });
      expect(getCustomerDeliveryAddress(state, "025")).to.deep.equal({
        cdel: "025",
        name: "Baz",
      });
    });
  });
  describe("getCustomerDeliveryAddressesMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        erp: {
          customer: {
            deliveryAddresses: [
              {
                cdel: "001",
                name: "name1",
                nameExtra: null,
                address: "address1",
                addressExtra: null,
                city: "city1",
                cityExtra: null,
                country: "FR",
              },
              {
                cdel: "002",
                name: "name2",
                nameExtra: "nameExtra2",
                address: "address2",
                addressExtra: "addressExtra2",
                city: "city2",
                cityExtra: "cityExtra2",
                country: "US",
              },
            ],
          },
        },
      };
      expect(getCustomerDeliveryAddressesMapping(initialState)).to.deep.equal(
        []
      );
      expect(getCustomerDeliveryAddressesMapping.recomputations()).to.equal(1);
      expect(getCustomerDeliveryAddressesMapping(state)).to.deep.equal([
        { value: "001", label: "001 name1 address1 city1 FR" },
        {
          value: "002",
          label:
            "002 name2 nameExtra2 address2 addressExtra2 city2 cityExtra2 US",
        },
        { value: "oth", label: "Use another address" },
      ]);
      expect(getCustomerDeliveryAddressesMapping.recomputations()).to.equal(2);
      getCustomerDeliveryAddressesMapping(state);
      expect(getCustomerDeliveryAddressesMapping.recomputations()).to.equal(2);
    });
  });
  describe("getBusinessPartnersMapping", () => {
    it("should use memoization", () => {
      const state: any = {
        erp: {
          businessPartners: [
            { code: "SU1000", name: "Foo" },
            { code: "SU1001", name: "Bar" },
          ],
        },
      };
      expect(getBusinessPartnersMapping(initialState)).to.deep.equal([]);
      expect(getBusinessPartnersMapping.recomputations()).to.equal(1);
      expect(getBusinessPartnersMapping(state)).to.deep.equal([
        { value: "SU1000", name: "Foo", label: "SU1000 - Foo" },
        { value: "SU1001", name: "Bar", label: "SU1001 - Bar" },
      ]);
      expect(getBusinessPartnersMapping.recomputations()).to.equal(2);
      getBusinessPartnersMapping(state);
      expect(getBusinessPartnersMapping.recomputations()).to.equal(2);
    });
  });
});
