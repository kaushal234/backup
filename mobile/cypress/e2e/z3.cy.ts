describe("Warm Up Test 3", () => {
  cy.on("fail", (err) => {
    Cypress.log({
      name: "soft-fail",
      message: err.message,
    });
    return false;
  });

  cy.once("uncaught:exception", (err) => {
    Cypress.log({ name: "uncaught", message: err.message });
    return false;
  });

  it("Warm Up", () => {
    cy.visit("/");
    cy.wait(10000);
  });
});
