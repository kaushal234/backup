import { API_CALL } from "../constants/api";
import { checkFilesEmptyTab, checkFiles } from "../common/files";
import { postTocWithDummyData } from "../api/postToc";
import { FILE_PATH, TEST_USER_CSM } from "../constants/constants";
import { formFileUpload } from "../common/formFileUpload";

const checkMainFile = () => {
  cy.getBySel("detail-files-add-file").click();
  formFileUpload({
    fieldName: "main-file-upload-popup-file",
    setFiles: [{ file: FILE_PATH.sample1 }],
  });
  cy.getBySel("file-upload-popup-submit").click();
  cy.waitForApiWithLoader(API_CALL.postTocMainFile, 201);

  cy.getBySel("detail-files-0-action-edit").click();
  formFileUpload({
    fieldName: "main-file-upload-popup-file",
    setFiles: [{ file: FILE_PATH.sample3 }],
  });
  cy.getBySel("file-upload-popup-submit").click();
  cy.waitForApiWithLoader(API_CALL.postTocMainFile, 201);
};

const checkRegularFileDelete = () => {
  cy.getBySel("detail-files-1-action-delete").click();
  cy.waitForApiWithLoader(API_CALL.deleteTocFile, 204);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.postTocMainFile);
  cy.interceptApi(API_CALL.deleteTocFile);
  cy.visit("/");
  cy.loginWithUser(TEST_USER_CSM.username, TEST_USER_CSM.password);
  cy.window().then((window) => {
    postTocWithDummyData(window);
  });
};

describe("TOC Page", () => {
  it("Files Functionality", () => {
    setup();
    cy.get("@tocId").then((tocId) => {
      checkFilesEmptyTab(`/toc/details/${tocId}`);
      checkFiles(`/toc/details/${tocId}`, API_CALL.postTocFile, true);
      checkMainFile();
      checkRegularFileDelete();
    });
  });
});
