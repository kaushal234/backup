import { describe, it } from "mocha";
import { expect } from "chai";
import { masterSalesForecastArrayFactory } from "../../../../model/form/sfr/factoryMaster";

const dummySalesForecasts: any = {
  asm: { value: "/people/12747", label: "Ackland James" },
  sso: { value: "/locations/7", label: "TLD EUR", erp: 540, currency: "EUR" },
  salesForecasts: [
    {
      estimatedSaleDate: "2030-04-01T14:46:22.072Z",
      successPercentage: "95",
      customerSuccessPercentage: "10",
      factory: {
        value: "/locations/11",
        label: "TLD SHE",
        erp: 420,
        currency: "CAD",
      },
      product: {
        "@id": "/sales/products/740",
        "@type": "Product",
        name: "TMX-150",
        financeFamily: {
          value: "/finance/finance_families/311",
          label: "dadada",
        },
        dmsPhoto: null,
        value: "/sales/products/740",
        label: "TMX-150",
        index: 1,
      },
      tier: { value: "/emission_ratings/8", label: "Electric" },
      quantity: "12",
      comment: "This is a comment",
      price: "12",
      margin: "50",
    },
    {
      airport: null,
      estimatedSaleDate: "2030-04-01T14:46:22.072Z",
      customerSuccessPercentage: "10",
      factory: {
        value: "/locations/11",
        label: "TLD SHE",
        erp: 420,
        currency: "CAD",
      },
      product: {
        "@id": "/sales/products/41",
        "@type": "Product",
        name: "TMX-100",
        financeFamily: {
          value: "/finance/finance_families/297",
          label: "say my name",
        },
        dmsPhoto: null,
        value: "/sales/products/41",
        label: "TMX-100",
        index: 0,
      },
      tier: { value: "/emission_ratings/12", label: "LPG-CNG" },
      quantity: "14",
      comment: "This is another comment",
      price: "50",
      margin: "10",
    },
  ],
  equoteId: "4321",
  status: "BUDGET",
  buyer: { value: "/sales/customers/3552", label: "AIR FRANCE (AF)" },
  country: { value: "/countries/38", label: "Canada" },
  submit: true,
};

describe("Sales Forecast factory", () => {
  it("should return multiple Master Sales Forecast with one SFR when not synchronized", () => {
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    const results = masterSalesForecastArrayFactory(dummySalesForecasts).map(
      (msfr) => {
        msfr.salesForecasts = msfr.salesForecasts.map((sfr) => {
          sfr.estimatedSaleDate = null;
          return sfr;
        });
        return msfr;
      }
    );
    expect(results).to.deep.equal([
      {
        salesForecasts: [
          {
            airport: null,
            asm: "/people/12747",
            buyer: "/sales/customers/3552",
            comment: "This is a comment",
            country: "/countries/38",
            customerSuccessPercentage: 10,
            endUser: "/sales/customers/3552",
            equoteId: "4321",
            estimatedSaleDate: null,
            factory: "/locations/11",
            margin: 50,
            price: 12,
            product: "/sales/products/740",
            quantity: 12,
            sso: "/locations/7",
            status: "BUDGET",
            successPercentage: 95,
            synchronized: false,
            thirdParty: null,
            tier: "/emission_ratings/8",
          },
        ],
      },
      {
        salesForecasts: [
          {
            airport: null,
            asm: "/people/12747",
            buyer: "/sales/customers/3552",
            comment: "This is another comment",
            country: "/countries/38",
            customerSuccessPercentage: 10,
            endUser: "/sales/customers/3552",
            equoteId: "4321",
            estimatedSaleDate: null,
            factory: "/locations/11",
            margin: 10,
            price: 50,
            product: "/sales/products/41",
            quantity: 14,
            sso: "/locations/7",
            status: "BUDGET",
            successPercentage: 0,
            synchronized: false,
            thirdParty: null,
            tier: "/emission_ratings/12",
          },
        ],
      },
    ]);
  });
  it("should return a single Master Sales Forecast when synchronized", () => {
    dummySalesForecasts.synchronized = true;
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    const results = masterSalesForecastArrayFactory(dummySalesForecasts).map(
      (msfr) => {
        msfr.salesForecasts = msfr.salesForecasts.map((sfr) => {
          sfr.estimatedSaleDate = null;
          return sfr;
        });
        return msfr;
      }
    );
    expect(results).to.deep.equal([
      {
        salesForecasts: [
          {
            airport: null,
            asm: "/people/12747",
            buyer: "/sales/customers/3552",
            comment: "This is a comment",
            country: "/countries/38",
            customerSuccessPercentage: 10,
            endUser: "/sales/customers/3552",
            equoteId: "4321",
            estimatedSaleDate: null,
            factory: "/locations/11",
            margin: 50,
            price: 12,
            product: "/sales/products/740",
            quantity: 12,
            sso: "/locations/7",
            status: "BUDGET",
            successPercentage: 95,
            synchronized: true,
            thirdParty: null,
            tier: "/emission_ratings/8",
          },
          {
            airport: null,
            asm: "/people/12747",
            buyer: "/sales/customers/3552",
            comment: "This is another comment",
            country: "/countries/38",
            customerSuccessPercentage: 10,
            endUser: "/sales/customers/3552",
            equoteId: "4321",
            estimatedSaleDate: null,
            factory: "/locations/11",
            margin: 10,
            price: 50,
            product: "/sales/products/41",
            quantity: 14,
            sso: "/locations/7",
            status: "BUDGET",
            successPercentage: 0,
            synchronized: true,
            thirdParty: null,
            tier: "/emission_ratings/12",
          },
        ],
      },
    ]);
  });
  it("should exclude a SFR if it's already submitted", () => {
    dummySalesForecasts.synchronized = false;
    dummySalesForecasts.salesForecasts[0].submitted = true;
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    const results = masterSalesForecastArrayFactory(dummySalesForecasts).map(
      (msfr) => {
        msfr.salesForecasts = msfr.salesForecasts.map((sfr) => {
          sfr.estimatedSaleDate = null;
          return sfr;
        });
        return msfr;
      }
    );
    expect(results).to.deep.equal([
      {
        salesForecasts: [
          {
            airport: null,
            asm: "/people/12747",
            buyer: "/sales/customers/3552",
            comment: "This is another comment",
            country: "/countries/38",
            customerSuccessPercentage: 10,
            endUser: "/sales/customers/3552",
            equoteId: "4321",
            estimatedSaleDate: null,
            factory: "/locations/11",
            margin: 10,
            price: 50,
            product: "/sales/products/41",
            quantity: 14,
            sso: "/locations/7",
            status: "BUDGET",
            successPercentage: 0,
            synchronized: false,
            thirdParty: null,
            tier: "/emission_ratings/12",
          },
        ],
      },
    ]);
  });
});
