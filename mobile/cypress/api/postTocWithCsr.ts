import { ITechnicianOnCall } from "../../src/@type/IPostTechnicalOnCallApiResponse";
import { API_METHOD } from "../constants/constants";
import { getToken } from "../utils/utils";

const url = `${Cypress.env("API_BASE_URL")}/service/technician_on_calls`;

const body = {
  originalTitle: "My Long Title",
  originalDescription: "<p>My Description</p>",
  equipmentRecord: "/equipment_records/1",
  assignee: "/people/64",
  technician: "/people/64",
  airport: "/airports/62",
  errorCodes: "Code 1",
  unitOperationalStatus: "/unit_operational_statuses/MCF",
  technicianOnCallType: "/service/technician_on_call_types/2",
  serviceActivity: "/service/service_activities/1",
  indiceFactor: "IF 1",
  salesOrganisationService: "/locations/23",
  tags: ["/technician_on_call_tags/4"],
  nestedCustomerServiceRecord: {
    leader: "/people/64",
    plannedAt: "2025-01-01",
  },
  mainContact: "/sales/extranet_users/203",
  contacts: ["/sales/extranet_users/207"],
  hourMeter: 10,
  customer: "/sales/customers/1",
  thirdPartyName: "My Third Party",
  thirdPartyRef: "My Third Party Ref",
  serialNumber: "My Customer Asset Number",
};

export const postTocAndCsrWithDummyData = (window: Cypress.AUTWindow) => {
  const token = getToken(window);
  if (token) {
    cy.request({
      method: API_METHOD.POST,
      url,
      body,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }).then((response) => {
      const res = response.body as ITechnicianOnCall;
      const tocId = res.id;
      const csrId = res["@sub_resources"]?.customerServiceRecord.id;
      cy.wrap(tocId).as("tocId");
      cy.wrap(csrId).as("csrId");
    });
  }
};
