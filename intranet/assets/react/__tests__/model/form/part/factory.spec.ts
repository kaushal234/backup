import { describe, it } from "mocha";
import { expect } from "chai";
import { partObjectFactory } from "../../../../model/form/part/partObjectFactory";

const dummyParts = {
  parts: [
    {
      partNumber: {
        value: "/foo/2",
        item: "123456",
        itemDescription: "dummy description",
      },
      unitOfMeasure: "EA",
      quantity: 2,
      reference: "WO",
      referenceNumber: "1234",
      serialNumber: "serial test",
      vendorSerialNumber: "123456",
      vendorPartNumber: "123456",
      failureType: "ELECTRICAL",
      failureSystem: "HYDRAULIC",
      ship: true,
      receivedQuantity: 8,
      bar: "foo",
    },
  ],
};

const dummyPartObject = {
  "@id": "/foo/1",
};
describe("Part object factory", () => {
  it("should return a SPR part object", () => {
    expect({
      ...partObjectFactory(dummyParts, dummyPartObject, "SPR"),
    }).to.deep.equal({
      "@id": "/foo/1",
      parts: [
        {
          "@id": null,
          id: null,
          partNumber: "123456",
          description: "dummy description",
          unitOfMeasure: "EA",
          quantity: 2,
        },
      ],
    });
  });
  it("should return a NCR part object", () => {
    expect({
      ...partObjectFactory(dummyParts, dummyPartObject, "NCR"),
    }).to.deep.equal({
      "@id": "/foo/1",
      parts: [
        {
          "@id": null,
          id: null,
          partNumber: "123456",
          description: "dummy description",
          unitOfMeasure: "EA",
          quantity: 2,
          reference: "WO",
          referenceNumber: "1234",
          serialNumber: "serial test",
        },
      ],
    });
  });
  it("should return a VWC part object", () => {
    expect({
      ...partObjectFactory(dummyParts, dummyPartObject, "VWC"),
    }).to.deep.equal({
      "@id": "/foo/1",
      parts: [
        {
          "@id": null,
          id: null,
          partNumber: "123456",
          description: "dummy description",
          unitOfMeasure: "EA",
          quantity: 2,
          serialNumber: "serial test",
          vendorSerialNumber: "123456",
          vendorPartNumber: "123456",
          failureType: "ELECTRICAL",
          failureSystem: "HYDRAULIC",
          ship: true,
          receivedQuantity: 8,
        },
      ],
    });
  });
});
