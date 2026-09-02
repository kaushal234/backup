import { defineConfig } from "cypress";
import cypressTerminalReport from "cypress-terminal-report/src/installLogsPrinter";

export default defineConfig({
  viewportHeight: 896,
  viewportWidth: 414,
  e2e: {
    setupNodeEvents(on) {
      cypressTerminalReport(on, {
        defaultTrimLength: 100000,
      });
    },
    baseUrl: "http://localhost:3000",
    numTestsKeptInMemory: 0,
    supportFile: "cypress/support/e2e.ts",
  },
  screenshotOnRunFailure: true,
  defaultCommandTimeout: 60000,
  video: false,
  requestTimeout: 60000,
  responseTimeout: 60000,
  retries: {
    runMode: 2,
  },
});
