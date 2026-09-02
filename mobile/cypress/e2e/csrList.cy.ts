import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";

const checkBasicListing = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.csrList.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.csrList.breadcrumbs[idx]);
    });
  cy.getBySel("csr-list-results")
    .invoke("text")
    .should("match", /Results \(\d+\)/i);
  cy.getBySel("csr-list-no-data").contains(TEXT.csrList.noCsrAvailable);
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200);
  cy.getBySel("csr-list-refresh").click();
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200);

  cy.getBySel("csr-card").should("have.length", 5);

  cy.getBySel("csr-card")
    .first()
    .findBySel("csr-card-value")
    .should("have.length", 8)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.csrList.cardValues[idx]);
    });
};

const checkPagination = () => {
  cy.getByClassName("csr-list-pagination", "li")
    .should("have.length.at.least", 4)
    .eq(2)
    .click();
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200);
  cy.getBySel("csr-card").should("have.length.at.least", 1);
  cy.getByClassName("csr-list-pagination", "li")
    .eq(2)
    .find("button")
    .should("have.class", "Mui-selected");
};

const checkSorting = () => {
  const noOfSort = TEXT.csrList.sortValue.length;
  cy.getBySel("csr-list-sort-icon").click();
  cy.getBySelLike("csr-list-sort-option-").should("have.length", noOfSort);
  cy.getBySelLike("csr-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
  cy.waitForApiWithLoader(API_CALL.getCsrList, 200);

  const params: Array<{ [key: string]: string }> = [
    { "order[airport.code]": "ASC" },
    { "order[airport.code]": "DESC" },
    { "order[createdAt]": "ASC" },
    { "order[createdAt]": "DESC" },
    { "order[updatedAt]": "ASC" },
    { "order[updatedAt]": "DESC" },
  ];

  for (let i = 0; i < noOfSort; i++) {
    cy.getBySel("csr-list-sort-icon").click();
    cy.getBySelLike("csr-list-sort-option-")
      .eq(i)
      .contains(TEXT.csrList.sortValue[i])
      .click();
    cy.waitForApiWithLoader(API_CALL.getCsrList, 200, params[i]);
  }

  cy.getBySel("csr-list-sort-icon").click();
  cy.getBySelLike("csr-list-sort-option-")
    .eq(noOfSort - 1)
    .click();
};

const checkNavigation = () => {
  cy.getBySel("csr-card").eq(0).click();
  cy.url().should("include", TEXT.csrList.cardLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.getCsrList);
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/csr");
};

describe("CSR List Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkBasicListing();
    checkPagination();
    checkSorting();
    checkNavigation();
  });
});
