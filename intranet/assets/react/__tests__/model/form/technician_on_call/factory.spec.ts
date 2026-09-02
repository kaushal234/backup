import { describe, it } from "mocha";
import { expect } from "chai";
import moment from "moment";
import {
  technicianOnCallWriteFactory,
  technicianOnCallFormFactory,
} from "../../../../model/form/technician_on_call/factory";

const dummyWriteToc = {
  equipmentRecord: { value: "/equipment_records/42" },
  salesOrganisationService: { value: "/locations/23" },
  airport: { value: "/airports/62" },
  assignee: { value: "/people/15" },
  technician: { value: "/people/15" },
  indiceFactor: { value: "IF 1" },
  hourMeter: 77,
  technicianOnCallType: { value: "/technician_on_call_types/2" },
  serviceActivity: { value: "/service_activities/5" },
  unitOperationalStatus: { value: "/unit_operational_statuses/1" },
  originalTitle: "Test TOC",
  originalDescription: "This is a test TOC",
  errorCodes: ["E01", "E02"],
  tags: [{ value: "/tags/1" }, { value: "/tags/2" }],
  customer: { value: "/sales/customers/1" },
  thirdPartyName: "AIR WAYS",
  thirdPartyRef: "EXT-REF-001",
  mainContact: { value: "/sales/extranet_users/77" },
  contacts: [
    {
      value: "/sales/extranet_users/78",
      label: "Barret Wallace",
      asMaintainer: true,
    },
    {
      value: "/sales/extranet_users/79",
      label: "Vincent Valentine",
      asUser: true,
    },
  ],
  serialNumber: "SN_AU_PIF",
  confidential: false,
};

describe("technicianOnCallWriteFactory", () => {
  it("should create a TOC object for writing", () => {
    const result = technicianOnCallWriteFactory(dummyWriteToc);
    expect(result).to.deep.equal({
      originalTitle: "Test TOC",
      originalDescription: "This is a test TOC",
      equipmentRecord: "/equipment_records/42",
      assignee: "/people/15",
      technician: "/people/15",
      airport: "/airports/62",
      errorCodes: ["E01", "E02"],
      unitOperationalStatus: "/unit_operational_statuses/1",
      technicianOnCallType: "/technician_on_call_types/2",
      serviceActivity: "/service_activities/5",
      indiceFactor: "IF 1",
      hourMeter: 77,
      salesOrganisationService: "/locations/23",
      tags: ["/tags/1", "/tags/2"],
      thirdPartyName: "AIR WAYS",
      thirdPartyRef: "EXT-REF-001",
      mainContact: "/sales/extranet_users/77",
      contacts: ["/sales/extranet_users/78", "/sales/extranet_users/79"],
      customer: "/sales/customers/1",
      serialNumber: "SN_AU_PIF",
      confidential: false,
    });
  });

  it("should include id if provided", () => {
    const result = technicianOnCallWriteFactory({ ...dummyWriteToc, id: 42 });
    expect(result).to.deep.equal({
      "@id": "/service/technician_on_calls/42",
      id: 42,
      originalTitle: "Test TOC",
      originalDescription: "This is a test TOC",
      equipmentRecord: "/equipment_records/42",
      assignee: "/people/15",
      technician: "/people/15",
      airport: "/airports/62",
      errorCodes: ["E01", "E02"],
      unitOperationalStatus: "/unit_operational_statuses/1",
      technicianOnCallType: "/technician_on_call_types/2",
      serviceActivity: "/service_activities/5",
      indiceFactor: "IF 1",
      hourMeter: 77,
      salesOrganisationService: "/locations/23",
      tags: ["/tags/1", "/tags/2"],
      customer: "/sales/customers/1",
      thirdPartyName: "AIR WAYS",
      thirdPartyRef: "EXT-REF-001",
      mainContact: "/sales/extranet_users/77",
      contacts: ["/sales/extranet_users/78", "/sales/extranet_users/79"],
      serialNumber: "SN_AU_PIF",
      confidential: false,
    });
  });

  it("should handle nestedCustomerServiceRecord", () => {
    const nestedCsr = {
      leader: { value: "/people/1" },
      plannedAt: moment("2023-10-01T10:00:00Z").format("YYYY-MM-DDTHH:mm:ssZ"),
    };
    const result: any = technicianOnCallWriteFactory({
      ...dummyWriteToc,
      nestedCustomerServiceRecord: nestedCsr,
    });
    expect(result.nestedCustomerServiceRecord).to.deep.equal({
      leader: "/people/1",
      plannedAt: moment("2023-10-01T10:00:00Z").format("YYYY-MM-DDTHH:mm:ssZ"),
    });
  });

  it("should handle missing properties", () => {
    const result = technicianOnCallWriteFactory({
      ...dummyWriteToc,
      assignee: null,
      technician: null,
      tags: null,
    });
    expect(result.assignee).to.be.null;
    expect(result.technician).to.be.null;
    expect(result.tags).to.deep.equal([]);
  });

  it("should handle invalid date format in nestedCustomerServiceRecord", () => {
    const nestedCsr = {
      leader: { value: "/people/1" },
      plannedAt: "invalid-date",
    };
    const result: any = technicianOnCallWriteFactory({
      ...dummyWriteToc,
      nestedCustomerServiceRecord: nestedCsr,
    });
    expect(result.nestedCustomerServiceRecord.plannedAt).to.be.null;
  });
});

const dummyFormToc = {
  equipmentRecord: {
    "@id": "/equipment_records/42",
    serialNumber: "SN123",
    hourMeter: 5090,
    salesOrganisationService: { "@id": "/locations/23", name: "Location 23" },
  },
  salesOrganisationService: { "@id": "/locations/23", name: "Location 23" },
  airport: { "@id": "/airports/62", code: "ABC", cityName: "CityName" },
  assignee: { "@id": "/people/15", firstname: "John", lastname: "Doe" },
  technician: { "@id": "/people/15", firstname: "John", lastname: "Doe" },
  indiceFactor: { value: "IF 10" },
  technicianOnCallType: {
    "@id": "/technician_on_call_types/2",
    name: "Type 2",
  },
  serviceActivity: { "@id": "/service_activities/5", name: "Activity 5" },
  unitOperationalStatus: {
    "@id": "/unit_operational_statuses/1",
    name: "Status 1",
  },
  title: "Test TOC",
  description: "This is a test TOC",
  errorCodes: ["E01", "E02"],
  tags: [
    { "@id": "/tags/1", name: "Tag 1" },
    { "@id": "/tags/2", name: "Tag 2" },
  ],
  customer: { "@id": "/sales/customers/1", name: "Delta" },
  thirdPartyName: "AIR WAYS",
  thirdPartyRef: "EXT-REF-001",
  mainContact: {
    "@id": "/sales/extranet_users/77",
    firstname: "Tifa",
    lastname: "Lockheart",
    asBuyer: true,
    asUser: true,
  },
  contacts: [
    {
      "@id": "/sales/extranet_users/78",
      firstname: "Barret",
      lastname: "Wallace",
      asMaintainer: true,
    },
    {
      "@id": "/sales/extranet_users/79",
      firstname: "Vincent",
      lastname: "Valentine",
      asUser: true,
    },
  ],
  serialNumber: "SN_AU_PIF",
  confidential: false,
};

describe("technicianOnCallFormFactory", () => {
  it("should create a TOC form object", () => {
    const result = technicianOnCallFormFactory(dummyFormToc);
    expect(result).to.deep.equal({
      id: undefined,
      originalTitle: "Test TOC",
      originalDescription: "This is a test TOC",
      equipmentRecord: {
        value: "/equipment_records/42",
        label: "SN123",
        hourMeter: 5090,
        salesOrganisationService: {
          "@id": "/locations/23",
          name: "Location 23",
        },
      },
      assignee: { value: "/people/15", label: "Doe, John" },
      technician: { value: "/people/15", label: "Doe, John" },
      airport: { value: "/airports/62", label: "ABC - CityName" },
      errorCodes: ["E01", "E02"],
      unitOperationalStatus: {
        value: "/unit_operational_statuses/1",
        label: "Status 1",
      },
      technicianOnCallType: {
        value: "/technician_on_call_types/2",
        label: "Type 2",
      },
      serviceActivity: { value: "/service_activities/5", label: "Activity 5" },
      salesOrganisationService: {
        value: "/locations/23",
        label: "Location 23",
      },
      indiceFactor: {
        label: "IF 10",
        value: "IF 10",
      },
      hourMeter: 5090,
      tags: [
        { value: "/tags/1", label: "Tag 1" },
        { value: "/tags/2", label: "Tag 2" },
      ],
      customer: { value: "/sales/customers/1", label: "Delta" },
      thirdPartyName: "AIR WAYS",
      thirdPartyRef: "EXT-REF-001",
      mainContact: {
        value: "/sales/extranet_users/77",
        label: "Lockheart Tifa (B - U)",
      },
      contacts: [
        { value: "/sales/extranet_users/78", label: "Wallace Barret (M)" },
        { value: "/sales/extranet_users/79", label: "Valentine Vincent (U)" },
      ],
      serialNumber: "SN_AU_PIF",
      confidential: false,
    });
  });

  it("should handle missing properties", () => {
    const result = technicianOnCallFormFactory({
      ...dummyFormToc,
      assignee: null,
      technician: null,
      tags: null,
      confidential: false,
    });
    expect(result.technician).to.be.null;
    expect(result.tags).to.deep.equal([]);
  });
});
