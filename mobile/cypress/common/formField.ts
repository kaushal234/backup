export const formField = (data: {
  fieldName: string;
  isTextArea?: boolean;
  checkLabel?: string;
  checkValue?: string;
  clear?: boolean;
  requiredError?: string;
  setValue?: string;
}) => {
  const type = data.isTextArea ? "textarea[aria-invalid]" : "input";
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }
  if (data.checkValue) {
    cy.getBySel(`${data.fieldName}-field`)
      .find(type)
      .should("have.value", data.checkValue);
  }
  if (data.clear) {
    cy.getBySel(`${data.fieldName}-field`).find(type).clear();
  }
  if (data.requiredError) {
    cy.getBySel(`${data.fieldName}-field`).find(type).focus().blur();
    cy.getBySel(`${data.fieldName}-field`).contains(data.requiredError);
  }
  if (data.setValue) {
    cy.getBySel(`${data.fieldName}-field`).find(type).type(data.setValue);
  }
};
