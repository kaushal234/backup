import { describe, it } from "mocha";
import { expect } from "chai";
import { productManufacturingFactory } from "../../../../model/form/product_manufacturing/factory";

const dummyProduct = {
  productManufacturings: {
    "/factory/1-/sales/products/1-2019": {
      "@id": "/foo/1",
      factoryStandardEfficiency: 9,
      industrialIncorporationParameter: 10,
      modelBaseHours: 150,
      effectiveAt: "2019-01-01T05:00:00.000Z",
    },
    "/factory/2-/sales/products/1-2019": {
      "@id": "/foo/2",
      factoryStandardEfficiency: 9,
      industrialIncorporationParameter: 10,
      modelBaseHours: 150,
      effectiveAt: "2019-01-01T05:00:00.000Z",
    },
  },
  bar: "bar",
};
describe("Product manufacturing update factory", () => {
  it("should return a Product", () => {
    expect({
      ...productManufacturingFactory({ ...dummyProduct, id: 21 }, "2020"),
    }).to.deep.equal({
      id: 21,
      productManufacturings: [
        {
          "@id": "/foo/1",
          factoryStandardEfficiency: 9,
          industrialIncorporationParameter: 10,
          factory: "/factory/1",
          modelBaseHours: 150,
          effectiveAt: "2019-01-01T05:00:00.000Z",
        },
        {
          "@id": "/foo/2",
          factoryStandardEfficiency: 9,
          industrialIncorporationParameter: 10,
          factory: "/factory/2",
          modelBaseHours: 150,
          effectiveAt: "2019-01-01T05:00:00.000Z",
        },
      ],
    });
  });
});
