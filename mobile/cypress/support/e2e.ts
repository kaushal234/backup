import "./commands";
import "cypress-real-events";
import cypressTerminalReport from "cypress-terminal-report/src/installLogsCollector";

cypressTerminalReport();

before(() => {
  if (typeof cy.clearIndexedDB !== "function") {
    cy.log("cy.clearIndexedDB() not available — waiting 10 seconds...");
    cy.wait(10000);
  }
});
