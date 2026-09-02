import { API_CALL } from "../constants/api";
import { checkFiles, checkFilesEmptyTab } from "../common/files";
import { TEST_USER_CSM } from "../constants/constants";
import { postTocAndCsrWithDummyData } from "../api/postTocWithCsr";

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocAndCsrWithDummyData(window);
  });
};

describe("CSR Page", () => {
  it("Files Functionality", () => {
    setup();
    cy.get("@csrId").then((csrId) => {
      checkFilesEmptyTab(`/csr/details/${csrId}`);
      checkFiles(`/csr/details/${csrId}`, API_CALL.postCsrFile);
    });
  });
});
