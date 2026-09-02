import { TEST_USER_CSM } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkReset = () => {
  cy.getBySel("profile-menu").click();
  cy.getBySel("profile-menu-settings").click();

  cy.getBySel("settings-heading").contains(TEXT.settings.heading);
  cy.getBySel("settings-reset").contains(TEXT.settings.reset.title);
  cy.getBySel("settings-reset").find("button").click();

  cy.getBySel("confirmation-popup-title").contains(
    TEXT.settings.reset.popup.title
  );
  cy.getBySel("confirmation-popup-description").contains(
    TEXT.settings.reset.popup.description
  );
  cy.getBySel("confirmation-popup-button-yes").click();

  cy.url().should("include", TEXT.settings.reset.redirectLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password, true);
};

describe("TOC Page", () => {
  it("Settings Functionality", () => {
    setup();
    checkReset();
  });
});
