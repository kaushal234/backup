import { ITechnicianOnCall } from "../../src/@type/IPostTechnicalOnCallApiResponse";
import { API_METHOD } from "../constants/constants";
import { getToken } from "../utils/utils";

const url = `${Cypress.env("API_BASE_URL")}/service/technician_on_call_parts`;

const body = {
  partNumber: "1",
  vendorPartNumber: "2",
  description: "my description",
  quantity: 1,
  comment: "my comment",
  replacement: "SUPPLIER",
  technicianOnCall: "",
};

export const postTocPart = (
  window: Cypress.AUTWindow,
  tocId: string | JQuery<HTMLElement>
) => {
  const token = getToken(window);
  if (token) {
    body.technicianOnCall = `/service/technician_on_calls/${tocId}`;
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
