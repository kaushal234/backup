import { formField } from "../common/formField";
import { TEST_USER_BASIC } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkLogin = () => {
  cy.getBySel("login-logo").should("be.visible");

  formField({
    fieldName: "login-username",
    checkLabel: TEXT.login.email.title,
    requiredError: TEXT.login.email.error,
    setValue: TEST_USER_BASIC.username,
  });

  formField({
    fieldName: "login-password",
    checkLabel: TEXT.login.password.title,
    requiredError: TEXT.login.password.error,
    setValue: TEST_USER_BASIC.password,
  });

  cy.getBySel("login-submit-button").contains(TEXT.login.submit).click();

  cy.get(".notistack-SnackbarContainer").contains(TEXT.login.toast);

  cy.getBySel("main-drawer-profile-wrapper").click();
};

const checkDrawer = () => {
  cy.getBySel("home-app-bar-menu").click();
  cy.getBySel("main-drawer").should("be.visible");
  cy.getBySelLike("drawer-").should("have.length", 6);
  cy.getBySel("main-drawer-profile-image").should("be.visible");
  cy.getBySel("main-drawer-profile-wrapper").contains(TEXT.profile.username);
  cy.getBySelLike("drawer-").first().click();
  cy.getBySel("main-drawer").should("not.exist");
};

const checkThemes = () => {
  cy.getBySel("profile-menu").click();
  cy.getBySel("theme-selector-system").click();
  cy.get("html").should("have.class", "light");

  cy.getBySel("profile-menu").click();
  cy.getBySel("theme-selector-dark").click();
  cy.get("html").should("have.class", "dark");

  cy.getBySel("profile-menu").click();
  cy.getBySel("theme-selector-light").click();
  cy.get("html").should("have.class", "light");
};

const checkLanguage = () => {
  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-zh").click();
  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-zh").contains(TEXT.language.chinese);
  cy.getBySel("language-selector-zh").click();

  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-fr").click();
  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-fr").contains(TEXT.language.french);
  cy.getBySel("language-selector-fr").click();

  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-en").click();
  cy.getBySel("profile-menu").click();
  cy.getBySel("language-selector-en").contains(TEXT.language.english);
  cy.getBySel("language-selector-en").click();
};

const checkProfilePage = () => {
  cy.getBySel("profile-menu").click();
  cy.getBySel("profile-menu-profile").contains(TEXT.profile.menu);
  cy.getBySel("profile-menu-profile").click();

  cy.getBySel("profile-avatar").should("be.visible");
  cy.getBySel("profile-full-name").invoke("text").should("not.be.empty");
  cy.getBySel("profile-info-label").should("have.length", 6);
  cy.getBySel("profile-info-value")
    .should("have.length", 6)
    .each(($item) => {
      cy.wrap($item).invoke("text").should("not.be.empty");
    });
};

const checkLogout = () => {
  cy.getBySel("profile-menu").click();
  cy.getBySel("profile-menu-logout").click();
  cy.get(".notistack-SnackbarContainer").contains(TEXT.logout.toast);
  cy.getBySel("login-username-label").should("be.visible");
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
};

describe("Login", () => {
  it("Basic Functionality", () => {
    setup();
    checkLogin();
    checkDrawer();
    checkThemes();
    checkLanguage();
    checkProfilePage();
    checkLogout();
  });
});
