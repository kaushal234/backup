import { postTocAndCsrWithDummyData } from "../api/postTocWithCsr";
import { checkLogs } from "../common/logs";
import { TEST_USER_CSM } from "../constants/constants";

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocAndCsrWithDummyData(window);
  });
};

describe("CSR Page", () => {
  it("Logs Functionality", () => {
    setup();
    cy.get("@csrId").then((csrId) => {
      checkLogs(`/csr/details/${csrId}`);
    });
  });
});
