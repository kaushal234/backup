import { API_CALL } from "../constants/api";
import { TEST_USER_AST_BU } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkBasicListing = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.home.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.home.breadcrumbs[idx]);
    });
  cy.getBySel("home-results").invoke("text").should("match", TEXT.home.heading);
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);
  cy.getBySel("home-refresh").click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);

  cy.getBySel("toc-card").should("have.length", 5);

  cy.getBySel("toc-card")
    .first()
    .findBySel("toc-card-value")
    .should("have.length", 8)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.home.cardValues[idx]);
    });

  cy.getBySel("toc-card")
    .first()
    .findBySel("toc-card-image")
    .should("be.visible");
};

const checkPagination = () => {
  cy.getByClassName("home-list-pagination", "li")
    .should("have.length.at.least", 4)
    .eq(2)
    .click();
  cy.waitForApiWithLoader(API_CALL.getTocList, 200);
  cy.getBySel("toc-card").should("have.length.at.least", 1);
  cy.getByClassName("home-list-pagination", "li")
    .eq(2)
    .find("button")
    .should("have.class", "Mui-selected");
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getTocList);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_AST_BU.username, TEST_USER_AST_BU.password, true);
};

describe("Home Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkBasicListing();
    checkPagination();
  });
});
