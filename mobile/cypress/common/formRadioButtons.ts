export const formRadioButtons = (data: {
  fieldName: string;
  checkLabel?: string;
  error?: string;
  checkValue?: number;
  selectIndex?: number;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-title`).contains(data.checkLabel);
  }
  if (data.checkValue) {
    cy.getBySel(`${data.fieldName}-option-${data.checkValue}`)
      .find("input")
      .should("have.value", "1");
  }
  if (data.error) {
    cy.getBySel(`${data.fieldName}-error`).contains(data.error);
  }
  if (data.selectIndex !== undefined) {
    cy.getBySel(`${data.fieldName}-option-${data.selectIndex}`)
      .find("input")
      .click();
  }
};
