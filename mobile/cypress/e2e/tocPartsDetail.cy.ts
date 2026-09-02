import { postTocWithDummyData } from "../api/postToc";
import { postTocPart } from "../api/postTocPart";
import { postTocSpr } from "../api/postTocSpr";
import { API_CALL } from "../constants/api";
import { TEST_USER_SUPER } from "../constants/constants";
import { TEXT } from "../constants/strings";

const checkPartsDetails = () => {
  cy.getBySel("toc-detail-parts-heading").contains(TEXT.tocPartsDetail.heading);

  cy.getBySel("toc-parts-0")
    .findBySelLike("-0-title")
    .each((element, idx) => {
      cy.wrap(element).contains(TEXT.tocPartsDetail.headers[idx]);
    });

  cy.getBySel("toc-parts-0")
    .findBySelLike("-0-value")
    .each((element, idx) => {
      if (TEXT.tocPartsDetail.values[idx] === "") return;
      cy.wrap(element).contains(TEXT.tocPartsDetail.values[idx]);
    });

  cy.getBySel("toc-parts-0")
    .findByIconIdLike("CheckIcon")
    .should("have.length", "1");
};

const checkSprDetails = () => {
  cy.getBySel("toc-detail-spr-heading").contains(
    TEXT.tocPartsDetail.spr.heading
  );

  cy.getBySel("toc-spr-0")
    .findBySelLike("-0-title")
    .each((element, idx) => {
      cy.wrap(element).contains(TEXT.tocPartsDetail.spr.headers[idx]);
    });

  cy.getBySel("toc-spr-0")
    .findBySelLike("-0-value")
    .each((element, idx) => {
      if (TEXT.tocPartsDetail.values[idx] === "") return;
      cy.wrap(element).contains(TEXT.tocPartsDetail.spr.values[idx]);
    });
};

const checkLinks = () => {
  cy.getBySel("toc-detail-parts-add").click();
  cy.url().should("match", TEXT.tocPartsDetail.links.create);

  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}?tab=parts`);
  });
  cy.getBySel("toc-parts-table-edit-0").click();

  cy.url().should("match", TEXT.tocPartsDetail.links.update);
};

const checkDelete = () => {
  cy.get("@tocId").then((tocId) => {
    cy.visit(`/toc/details/${tocId}?tab=parts`);
  });
  cy.getBySel("toc-parts-table-delete-0").click();

  cy.getBySel("confirmation-popup-title").contains(
    TEXT.tocPartsDetail.delete.heading
  );
  cy.getBySel("confirmation-popup-description").contains(
    TEXT.tocPartsDetail.delete.description
  );
  cy.getBySel("confirmation-popup-button-yes").click();

  cy.waitForApiWithLoader(API_CALL.deleteTocPart, 204);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.deleteTocPart);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_SUPER.username, TEST_USER_SUPER.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
  cy.get("@tocId").then((tocId) => {
    cy.window().then((window) => {
      postTocPart(window, tocId);
      postTocSpr(window, tocId);
    });
    cy.visit(`/toc/details/${tocId}?tab=parts`);
  });
};

describe("TOC Details Page - Parts Tab", () => {
  it("Basic Functionality", () => {
    setup();
    checkPartsDetails();
    checkSprDetails();
    checkLinks();
    checkDelete();
  });
});
