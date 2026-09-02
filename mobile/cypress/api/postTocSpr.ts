import { ITechnicianOnCall } from "../../src/@type/IPostTechnicalOnCallApiResponse";
import { API_METHOD } from "../constants/constants";
import { getToken } from "../utils/utils";

const url = `${Cypress.env("API_BASE_URL")}/parts/toc_spare_parts_requests`;

const body = {
  customer: "/sales/customers/36",
  sso: "/locations/23",
  factory: "/locations/29",
  erpLocation: "/locations/29",
  sph: "/locations/24",
  equipmentRecords: ["/equipment_records/1"],
  customers: ["/sales/customers/36", "/sales/customers/1"],
  airport: "/airports/62",
  sparePartsRequest: null,
  deliveryAddress: "/parts/spare_parts_request_delivery_addresses/1",
  deliveryNotes: "My Delivery Notes",
  parts: [
    {
      partNumber: "!TEMP1001",
      description: "DIODE",
      quantity: 1,
      unitOfMeasure: "EA",
      comment: "My Comment",
    },
  ],
  tocId: -1,
  activity: "Commissioning",
  type: "toc.type.customer",
  technicianOnCall: "/service/technician_on_calls/-1",
};

export const postTocSpr = (
  window: Cypress.AUTWindow,
  tocId: string | JQuery<HTMLElement>
) => {
  const token = getToken(window);
  if (token) {
    body.technicianOnCall = `/service/technician_on_calls/${tocId}`;
    body.tocId = +tocId;
    cy.request({
      method: API_METHOD.POST,
      url,
      body,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }).then((response) => {
      const res = response.body as ITechnicianOnCall;
      const tocPartId = res.id;
      cy.wrap(tocPartId).as("tocPartId");
    });
  }
};
