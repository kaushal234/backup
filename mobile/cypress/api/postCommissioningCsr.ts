import { ITechnicianOnCall } from "../../src/@type/IPostTechnicalOnCallApiResponse";
import { API_METHOD } from "../constants/constants";
import { getToken } from "../utils/utils";

const url = `${Cypress.env(
  "API_BASE_URL"
)}/service/commissioning_customer_service_records`;

const body = {
  title: "My Title",
  description: "<p>My Description</p>",
  equipmentRecord: "/equipment_records/1",
  airport: "/airports/62",
  leader: "/people/64",
  plannedAt: "01/01/2025",
};

export const postCommissioningCsr = (window: Cypress.AUTWindow) => {
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
      const csrId = res.id;
      cy.wrap(csrId).as("csrId");
    });
  }
};
