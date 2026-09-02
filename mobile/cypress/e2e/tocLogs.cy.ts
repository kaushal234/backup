import { postTocWithDummyData } from "../api/postToc";
import { checkLogs } from "../common/logs";
import { TEST_USER_CSM } from "../constants/constants";

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
};

describe("TOC Page", () => {
  it("Logs Functionality", () => {
    setup();
    cy.get("@tocId").then((tocId) => {
      checkLogs(`/toc/details/${tocId}`, true);
    });
  });
});
