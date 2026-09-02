/// <reference types="cypress" />
import { assert } from "chai";
import { TEST_USER_BASIC } from "../constants/constants";
import { TEXT } from "../constants/strings";
import { formField } from "../common/formField";
import { assertTextMatch } from "../utils/utils";

Cypress.Commands.add("getBySel", (selector, options) => {
  return cy.get(`[data-cy=${selector}]`, options);
});

Cypress.Commands.add("getBySelLike", (selector, ...args) => {
  return cy.get(`[data-cy^=${selector}]`, ...args);
});

Cypress.Commands.add(
  "loginWithUser",
  (
    username: string = TEST_USER_BASIC.username,
    password: string = TEST_USER_BASIC.password,
    closeDrawer = false
  ) => {
    formField({
      fieldName: "login-username",
      setValue: username,
    });
    formField({
      fieldName: "login-password",
      setValue: password,
    });
    cy.getBySel("login-submit-button").click();
    cy.get(".notistack-SnackbarContainer").contains(TEXT.login.toast);
    if (closeDrawer) {
      cy.getBySel("main-drawer-profile-wrapper").click();
    }
  }
);

Cypress.Commands.add("interceptApi", (api) => {
  cy.intercept(api.method, api.url).as(api.name);
});

Cypress.Commands.add(
  "waitForApiWithLoader",
  (api, code, params = null, skipLoader = false) => {
    const disableLoaderCheck = true;
    if (!skipLoader && !disableLoaderCheck) {
      cy.getBySel("main-loader").should("be.visible");
      cy.getBySel("main-loader").should("not.exist");
    }
    cy.wait(`@${api.name}`).then((interception) => {
      if (code) {
        expect(interception.response?.statusCode).to.equal(code);
      }
      if (params) {
        const apiParams = interception.request.url.split("?")[1];
        const searchParams = new URLSearchParams(apiParams);
        Object.keys(params).forEach((key) => {
          assert.isTrue(searchParams.has(key));
          assertTextMatch(searchParams.get(key) ?? "", params[key]);
        });
      }
    });
  }
);

Cypress.Commands.add(
  "findBySel",
  { prevSubject: true },
  (subject, selector) => {
    return subject.find(`[data-cy=${selector}]`);
  }
);

Cypress.Commands.add(
  "findBySelLike",
  { prevSubject: true },
  (subject, selector) => {
    return subject.find(`[data-cy*=${selector}]`);
  }
);

Cypress.Commands.add("getByClassName", (selector, className, ...args) => {
  return cy.get(`[data-cy=${selector}] ${className}`, ...args);
});

Cypress.Commands.add(
  "findByIconIdLike",
  { prevSubject: true },
  (subject, selector) => {
    return subject.find(`[data-testid^=${selector}]`);
  }
);

Cypress.Commands.add("clearIndexedDB", () => {
  cy.window().then((win) => {
    const getDbs = win.indexedDB.databases
      ? win.indexedDB.databases()
      : Promise.resolve([]);

    return getDbs.then((dbs) => {
      const deletions = dbs
        .filter((db): db is { name: string } => !!db.name)
        .map((db) => {
          return new Promise<void>((resolve, reject) => {
            const req = win.indexedDB.deleteDatabase(db.name);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
            req.onblocked = () => resolve();
          });
        });

      return Promise.all(deletions);
    });
  });
});
