export const formDatePicker = (data: {
  fieldName: string;
  checkLabel?: string;
  checkValue?: string;
  clear?: boolean;
  requiredError?: string;
  setValue?: string;
  valueError?: string;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }
  if (data.checkValue) {
    cy.getBySel(`${data.fieldName}-date-picker`)
      .find("input")
      .should("have.value", data.checkValue);
  }
  if (data.clear) {
    cy.getBySel(`${data.fieldName}-date-picker`)
      .findByIconIdLike("ClearIcon")
      .click();
  }
  if (data.requiredError) {
    cy.getBySel(`${data.fieldName}-date-picker`).click();
    cy.getBySel(`${data.fieldName}-date-picker`)
      .find("input")
      .type("2025-01-01");
    cy.getBySel(`${data.fieldName}-date-picker`)
      .find(`[data-testid^=ClearIcon]`)
      .click();
    cy.getByClassName(
      `${data.fieldName}-date-picker`,
      `#${data.fieldName}-helper-text`
    ).contains(data.requiredError);
  }
  if (data.setValue) {
    cy.getBySel(`${data.fieldName}-date-picker`)
      .find("input")
      .type(data.setValue);
  }
  if (data.valueError) {
    cy.getBySel(`${data.fieldName}-date-picker`).contains(data.valueError);
  }
};
