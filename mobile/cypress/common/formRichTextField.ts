export const checkRichTextArea = (
  fieldName: string,
  firstDescription: string,
  secondDescription: string,
  error: string
) => {
  cy.getBySel(fieldName).find(".ck-editor__editable").click();

  cy.get("body").click(0, 0);

  cy.getBySel(fieldName).find(".Mui-error").contains(error);

  cy.getBySel(fieldName).find(".ck-editor__editable").click();

  cy.realType(firstDescription);

  cy.getBySel(fieldName)
    .find(".ck-editor__editable")
    .contains(firstDescription);

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(4).click();

  cy.realType(secondDescription);

  cy.getBySel(fieldName)
    .find(".ck-editor__editable")
    .contains(secondDescription);

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(4).click();

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(5).click();

  cy.getBySel(fieldName)
    .find(".ck-editor__editable")
    .contains(secondDescription);

  for (let i = 0; i < 11; i++) {
    cy.realPress(["Shift", "ArrowLeft"]);
  }

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(0).click();

  cy.getBySel(fieldName).find(".ck-editor__editable").click();

  for (let i = 0; i < 12; i++) {
    cy.realPress("ArrowLeft");
  }
  for (let i = 0; i < 7; i++) {
    cy.realPress(["Shift", "ArrowLeft"]);
  }

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(1).click();

  cy.getBySel(fieldName).find(".ck-editor__editable").click();

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(2).click();

  cy.realPress("Enter");
  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(0).click();
  cy.realPress("Enter");

  cy.getBySel(fieldName).find(".ck-toolbar__items button").eq(3).click();

  cy.realType(firstDescription);
};

export const formRichTextField = (data: {
  fieldName: string;
  checkLabel?: string;
  clear?: number;
  requiredError?: string;
  setValue?: string;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }

  if (data.clear) {
    cy.getBySel(`${data.fieldName}-field`).find(".ck-editor__editable").click();
    for (let i = 0; i < data.clear; i++) {
      cy.realPress(["Shift", "ArrowLeft"]);
    }
    cy.realPress(["Delete"]);
  }

  if (data.requiredError) {
    cy.getBySel(`${data.fieldName}-field`).find(".ck-editor__editable").click();
    cy.getBySel(`${data.fieldName}-field`).parent().click("top");
    cy.getBySel(`${data.fieldName}-field`)
      .find(".Mui-error")
      .contains(data.requiredError);
  }

  if (data.setValue) {
    cy.getBySel(`${data.fieldName}-field`).find(".ck-editor__editable").click();
    cy.realType(data.setValue);
  }
};
