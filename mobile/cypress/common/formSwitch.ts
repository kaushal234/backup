export const formSwitch = (data: {
  fieldName: string;
  checkLabel?: string;
  checkValue?: boolean;
  toggle?: boolean;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }
  if (data.checkValue !== undefined) {
    cy.getByClassName(`${data.fieldName}-label`, ".MuiButtonBase-root").should(
      data.checkValue ? "have.class" : "not.have.class",
      "Mui-checked"
    );
  }
  if (data.toggle) {
    cy.getBySel(`${data.fieldName}-field`).find("input").click();
  }
};
