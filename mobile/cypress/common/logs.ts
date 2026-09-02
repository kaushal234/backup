import { generateRandomString } from "../utils/utils";
import { API_CALL } from "../constants/api";
import { DOWNLOADED_FILE_PATH, FILE_PATH } from "../constants/constants";
import { TEXT } from "../constants/strings";
import { checkFormFileUploadFileName, formFileUpload } from "./formFileUpload";
import { formSwitch } from "./formSwitch";
import { formRichTextField } from "./formRichTextField";

export const checkLogsEmptyTab = (url: string) => {
  cy.visit(url);
  cy.getBySel("tab-logs").click();
  cy.getBySel("logs-heading").contains(TEXT.logs.heading);
  cy.getBySel("logs-no-content").should("be.visible");
};

const checkAddLogWithImage = (logText: string, isToc = false) => {
  cy.getBySel("tab-logs").click();
  cy.getBySel("logs-add").click();

  cy.getBySel("add-log-popup-heading").should("be.visible");

  cy.getBySel("add-log-popup-log-label").should("be.visible");

  formRichTextField({
    fieldName: "add-log-popup-log",
    setValue: logText,
  });

  cy.getBySel("add-log-popup-file-title").should("be.visible");

  if (isToc) {
    formSwitch({
      fieldName: "add-log-popup-factory-flag",
      checkLabel: TEXT.logs.addPopup.factory.title,
      toggle: true,
    });
  }

  formFileUpload({
    fieldName: "add-log-popup-file",
    setFiles: [{ file: FILE_PATH.sample1 }],
  });

  checkFormFileUploadFileName({
    fieldName: "add-log-popup-file-value-0-file-name-field",
    value: TEXT.logs.addPopup.file.value,
  });
  cy.getBySel("add-log-popup-file-value-0-action-icon").click();

  formFileUpload({
    fieldName: "add-log-popup-file",
    setFiles: [{ file: FILE_PATH.sample1 }],
  });

  checkFormFileUploadFileName({
    fieldName: "add-log-popup-file-value-0-file-name-field",
    value: TEXT.logs.addPopup.file.value,
  });

  cy.getBySel("add-log-popup-file-value-0-thumbnail").click();
  cy.getBySel("image-viewer-image").should("be.visible");
  cy.getBySel("image-viewer-action").click();
  cy.readFile(DOWNLOADED_FILE_PATH.logs.sample1).should("exist");
  cy.get("body").click(0, 0);

  cy.getBySel("add-log-popup-notification-title").contains(
    TEXT.logs.addPopup.type.title
  );
  cy.getBySelLike("add-log-popup-notification-option-")
    .each((option, idx) => {
      cy.wrap(option).contains(TEXT.logs.addPopup.type.values[idx]);
    })
    .eq(2)
    .click();

  cy.getBySel("add-log-popup-submit").click();

  if (!isToc) {
    cy.getBySel("confirmation-popup-title").contains(
      TEXT.logs.confirmationPopup.title
    );
    cy.getBySel("confirmation-popup-description").contains(
      TEXT.logs.confirmationPopup.description
    );
    cy.getBySel("confirmation-popup-sub-description").contains(logText);

    cy.getBySel("confirmation-popup-button-no").click();
    cy.getBySel("add-log-popup-submit").click();
    cy.getBySel("confirmation-popup-button-yes").click();
  }
  cy.waitForApiWithLoader(API_CALL.postComment, 201);
};

const checkLogListWithImage = (logText: string, isToc = false) => {
  cy.getBySel("log-comment-0-position")
    .invoke("text")
    .should("match", TEXT.logs.log.position);
  cy.getBySel("log-comment-0-username").contains(TEXT.logs.log.username);
  if (isToc) {
    cy.getBySel("log-comment-0-factory-flag").contains(TEXT.logs.log.factory);
  }
  if (!isToc) {
    cy.getBySel("log-comment-0-type").contains(TEXT.logs.log.type);
  }
  cy.getBySel("log-comment-0-description").contains(logText);
  cy.getBySel("log-comment-0-file-file-name-text").contains(
    TEXT.logs.log.filename
  );
  cy.getBySel("log-comment-0-date")
    .invoke("text")
    .should("match", TEXT.logs.log.date);
  cy.wait(1000);
  cy.getBySel("log-comment-0-file-thumbnail-img").click();
  cy.getBySel("image-viewer-image").should("be.visible");
  cy.get("body").click(0, 0);
};

const checkAddLogWithFile = (logText: string) => {
  cy.getBySel("logs-add").click();
  formRichTextField({
    fieldName: "add-log-popup-log",
    setValue: logText,
  });
  formFileUpload({
    fieldName: "add-log-popup-file",
    setFiles: [{ file: FILE_PATH.sample2 }],
  });
  checkFormFileUploadFileName({
    fieldName: "add-log-popup-file-value-0-file-name-field",
    value: TEXT.logs.addPopup.file2.value,
  });
  cy.getBySel("add-log-popup-file-value-0-thumbnail").click();
  cy.readFile(DOWNLOADED_FILE_PATH.logs.sample2).should("exist");
  cy.getBySelLike("add-log-popup-notification-option-").eq(0).click();
  cy.getBySel("add-log-popup-submit").click();
  cy.waitForApiWithLoader(API_CALL.postComment, 201);
};

const checkLogListWithFile = (logText: string) => {
  cy.getBySel("log-comment-0-position")
    .invoke("text")
    .should("match", TEXT.logs.log.position);
  cy.getBySel("log-comment-0-username").contains(TEXT.logs.log.username);
  cy.getBySel("log-comment-0-description").contains(logText);
  cy.getBySel("log-comment-0-file-file-name-text").contains(
    TEXT.logs.log.filename2
  );
  cy.getBySel("log-comment-0-date")
    .invoke("text")
    .should("match", TEXT.logs.log.date);

  cy.getBySel("log-comment-0-file-action-icon").click();
  cy.readFile(DOWNLOADED_FILE_PATH.logs.repeatedSample2).should("exist");
};

export const checkLogs = (url: string, isToc = false) => {
  cy.interceptApi(API_CALL.postComment);
  cy.visit(url);
  const logText = generateRandomString();
  checkAddLogWithImage(logText, isToc);
  checkLogListWithImage(logText, isToc);
  const logText2 = generateRandomString();
  checkAddLogWithFile(logText2);
  checkLogListWithFile(logText2);
};
