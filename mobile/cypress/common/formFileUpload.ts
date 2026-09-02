import { FILE_PATH } from "../constants/constants";

type FilePathKey = keyof typeof FILE_PATH;
type FilePathValue = (typeof FILE_PATH)[FilePathKey];

export const formFileUpload = (data: {
  fieldName: string;
  checkLabel?: string;
  error?: string;
  setFiles?: Array<{ file: FilePathValue; description?: string }>;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-title`).contains(data.checkLabel);
  }
  if (data.error) {
    cy.getBySel(`${data.fieldName}-error`).contains(data.error);
  }
  if (data.setFiles && data.setFiles.length) {
    cy.getBySel(`${data.fieldName}-upload-button`)
      .find("input[type=file]")
      .selectFile(
        data.setFiles.map((fileData) => fileData.file),
        { force: true }
      );
    data.setFiles.forEach((fileData) => {
      if (fileData.description) {
        cy.getByClassName(
          `${data.fieldName}-value-0-description-field`,
          "input"
        ).type(fileData.description ?? "");
      }
    });
  }
};

export const checkFormFileUploadFileName = (data: {
  fieldName: string;
  value: string;
}) => {
  cy.getBySel(data.fieldName).find("input").should("have.value", data.value);
};
