import { generateRandomString } from "../utils/utils";
import { IApiCall } from "../@types/IApiCall";
import { DOWNLOADED_FILE_PATH, FILE_PATH } from "../constants/constants";
import { TEXT } from "../constants/strings";
import { checkFormFileUploadFileName, formFileUpload } from "./formFileUpload";

export const checkFilesEmptyTab = (url: string) => {
  cy.visit(url);
  cy.getBySel("tab-files").click();
  cy.getBySel("detail-files-heading").contains(TEXT.files.heading);
  cy.getBySel("detail-files-no-content").contains(TEXT.files.noFiles);
};

const checkAddFiles = (
  fileDescription: string,
  apiCall: IApiCall,
  isToc = false
) => {
  cy.getBySel("tab-files").click();
  cy.getBySel("detail-files-add-file").click();
  cy.getBySel("file-upload-popup-heading").contains(
    TEXT.files.addPopUp.heading
  );

  cy.getBySel("file-upload-popup-submit").click();

  formFileUpload({
    fieldName: isToc ? "main-file-upload-popup-file" : "file-upload-popup-file",
    error: TEXT.files.addPopUp.error,
  });

  formFileUpload({
    fieldName: "file-upload-popup-file",
    setFiles: [{ file: FILE_PATH.sample1 }, { file: FILE_PATH.sample2 }],
  });

  checkFormFileUploadFileName({
    fieldName: "file-upload-popup-file-value-0-file-name-field",
    value: TEXT.files.addPopUp.file.file1Value,
  });
  checkFormFileUploadFileName({
    fieldName: "file-upload-popup-file-value-1-file-name-field",
    value: TEXT.files.addPopUp.file.file2Value,
  });
  cy.getBySel("file-upload-popup-file-value-0-action-icon").click();
  checkFormFileUploadFileName({
    fieldName: "file-upload-popup-file-value-0-file-name-field",
    value: TEXT.files.addPopUp.file.file2Value,
  });

  formFileUpload({
    fieldName: "file-upload-popup-file",
    setFiles: [
      { file: FILE_PATH.sample1, description: fileDescription },
      { file: FILE_PATH.sample2 },
    ],
  });

  checkFormFileUploadFileName({
    fieldName: "file-upload-popup-file-value-0-file-name-field",
    value: TEXT.files.addPopUp.file.file1Value,
  });
  checkFormFileUploadFileName({
    fieldName: "file-upload-popup-file-value-1-file-name-field",
    value: TEXT.files.addPopUp.file.file2Value,
  });

  cy.getBySel("file-upload-popup-file-value-0-thumbnail").click();
  cy.getBySel("image-viewer-image").should("be.visible");
  cy.getBySel("image-viewer-action").click();
  cy.readFile(DOWNLOADED_FILE_PATH.files.sample1).should("exist");
  cy.get("body").click(0, 0);

  cy.getBySel("file-upload-popup-file-value-1-thumbnail").click();
  cy.readFile(DOWNLOADED_FILE_PATH.files.sample2).should("exist");

  cy.getBySel("file-upload-popup-submit").click();

  cy.waitForApiWithLoader(apiCall, 201);
};

const checkFileListWithImage = (fileDescription: string, apiCall: IApiCall) => {
  cy.getBySel("detail-files-add-file").click();
  formFileUpload({
    fieldName: "file-upload-popup-file",
    setFiles: [{ file: FILE_PATH.sample1, description: fileDescription }],
  });
  cy.getBySel("file-upload-popup-submit").click();
  cy.waitForApiWithLoader(apiCall, 201);

  cy.getBySel("detail-files-0-chip").contains(TEXT.files.list.chip);
  cy.wait(1000);
  cy.getBySel("detail-files-0-thumbnail-img").click();
  cy.getBySel("image-viewer-image").should("be.visible");
  cy.getBySel("image-viewer-action").click();
  cy.readFile(DOWNLOADED_FILE_PATH.files.sample1).should("exist");
  cy.get("body").click(0, 0);
  cy.getBySel("detail-files-0-file-type").contains(TEXT.files.list.type);
  cy.getBySel("detail-files-0-file-name").contains(
    new RegExp(
      `${TEXT.files.list.toc.file1.title}|${TEXT.files.list.csr.file1.title}`
    )
  );
  cy.getBySel("detail-files-0-file-description").contains(
    fileDescription.split("").join(" ")
  );
  cy.getBySel("detail-files-0-action-download").click();
  cy.readFile(DOWNLOADED_FILE_PATH.files.sample1).should("exist");
};

const checkFileListWithFile = (apiCall: IApiCall) => {
  cy.getBySel("detail-files-add-file").click();
  formFileUpload({
    fieldName: "file-upload-popup-file",
    setFiles: [{ file: FILE_PATH.sample2 }],
  });
  cy.getBySel("file-upload-popup-submit").click();
  cy.waitForApiWithLoader(apiCall, 201);

  cy.getBySel("detail-files-0-chip").contains(TEXT.files.list.chip);
  cy.getBySel("detail-files-0-thumbnail").should("be.visible");
  cy.getBySel("detail-files-0-file-type").contains(TEXT.files.list.type);
  cy.getBySel("detail-files-0-file-name").contains(
    new RegExp(
      `${TEXT.files.list.toc.file2.title}|${TEXT.files.list.csr.file2.title}`
    )
  );
  cy.getBySel("detail-files-0-file-description").contains(
    TEXT.files.list.fileDescription
  );
  cy.getBySel("detail-files-0-action-download").click();
  cy.readFile(DOWNLOADED_FILE_PATH.files.sample2).should("exist");
};

export const checkFiles = (url: string, apiCall: IApiCall, isToc = false) => {
  cy.interceptApi(apiCall);
  cy.visit(url);
  const fileDescription = generateRandomString();
  checkAddFiles(fileDescription, apiCall, isToc);
  checkFileListWithImage(fileDescription, apiCall);
  checkFileListWithFile(apiCall);
};
