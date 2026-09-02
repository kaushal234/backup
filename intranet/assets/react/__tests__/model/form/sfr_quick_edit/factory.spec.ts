import { describe, it } from "mocha";
import { expect } from "chai";
import salesForecastFactory from "../../../../model/form/sfr_quick_edit/factory";

const dummySalesForecast: any = {
  "@id": "/sales/sales_forecasts/28271",
  "@type": "SalesForecast",
  id: 28271,
  masterSalesForecast: {
    "@id": "/sales/master_sales_forecasts/110532",
    "@type": "MasterSalesForecast",
    legacyId: 166540,
  },
  status: "BUDGET",
  sso: {
    "@id": "/locations/39",
    "@type": "Location",
    name: "AERO Specialties",
    erp: 250,
    legacyId: 45,
  },
  factory: {
    "@id": "/locations/45",
    "@type": "Location",
    name: "Powervamp",
    erp: 220,
    legacyId: 55,
  },
  asm: {
    "@id": "/people/19",
    "@type": "People",
    username: "denis.bernard@tld-europe.com",
    email: "denis.bernard@tld-europe.com",
    legacyId: 44,
    firstname: "Denis",
    lastname: "BERNARD",
  },
  poster: {
    "@id": "/people/15",
    "@type": "People",
    username: "antoine.maguin@tld-group.com",
    email: "antoine.maguin@tld-group.com",
    legacyId: 31,
    firstname: "Antoine",
    lastname: "MAGUIN",
  },
  equoteId: null,
  buyer: {
    "@id": "/sales/customers/3617",
    "@type": "Customer",
    name: "TRANSAIR",
    country: {
      "@id": "/countries/194",
      "@type": "Country",
      name: "Senegal",
      legacyId: 194,
    },
    legacyId: 147,
  },
  endUser: {
    "@id": "/sales/customers/3617",
    "@type": "Customer",
    name: "TRANSAIR",
    country: {
      "@id": "/countries/194",
      "@type": "Country",
      name: "Senegal",
      legacyId: 194,
    },
    legacyId: 147,
  },
  country: {
    "@id": "/countries/176",
    "@type": "Country",
    name: "Portugal",
    legacyId: 176,
  },
  airport: null,
  product: {
    "@id": "/sales/products/677",
    "@type": "Product",
    family: {
      "@id": "/sales/product_families/125",
      "@type": "ProductFamily",
      name: "A.L.I",
      productType: {
        "@id": "/sales/product_types/4",
        "@type": "ProductType",
        englishName: "Loaders",
        legacyId: 11,
      },
      legacyId: 320,
      dmsPhoto: null,
    },
    name: "A.L.I.",
    legacyId: 739,
    dmsPhoto: null,
  },
  quantity: 12,
  estimatedSaleDate: "2018-11-14T10:48:28.326Z",
  customerSuccessPercentage: 55,
  successPercentage: 75,
  delinquent: false,
  legacyId: 28579,
  index: 0,
};
describe("Sales Forecast factory", () => {
  it("should return a Sales Forecast", () => {
    // this is a hack to avoid momentJS to generate distinct date and mark the test as failing sometimes
    expect({
      ...salesForecastFactory(dummySalesForecast),
      estimatedSaleDate: null,
    }).to.deep.equal({
      id: 28271,
      status: "BUDGET",
      comment: undefined,
      quantity: 12,
      customerSuccessPercentage: 55,
      successPercentage: 75,
      estimatedSaleDate: null,
      synchronized: false,
      notificationRestricted: false,
    });
  });
  it("should return a synchronized Sales Forecast with a comment", () => {
    dummySalesForecast.synchronized = true;
    dummySalesForecast.notificationRestricted = true;
    dummySalesForecast.comment = "Wowowowo stop it and break it down";
    expect({
      ...salesForecastFactory(dummySalesForecast),
      estimatedSaleDate: null,
    }).to.deep.equal({
      id: 28271,
      status: "BUDGET",
      comment: "Wowowowo stop it and break it down",
      quantity: 12,
      customerSuccessPercentage: 55,
      successPercentage: 75,
      estimatedSaleDate: null,
      synchronized: true,
      notificationRestricted: true,
    });
  });
});
