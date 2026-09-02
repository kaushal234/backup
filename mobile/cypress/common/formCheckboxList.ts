export const formCheckboxList = (data: {
  fieldName: string;
  checkLabel?: string;
  checkOptions?: Array<string>;
  error?: string;
  checkedIndex?: Array<number>;
  toggleIndex?: Array<number>;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-title`).contains(data.checkLabel);
  }
  if (data.checkOptions?.length) {
    data.checkOptions.forEach((option, idx) => {
      cy.getBySel(`${data.fieldName}-${idx}-option-label`).contains(option);
    });
  }
  if (data.error) {
    cy.getBySel(`${data.fieldName}-error`).contains(data.error);
  }
  if (data.checkedIndex?.length) {
    data.checkedIndex.forEach((index) => {
      cy.getByClassName(
        `${data.fieldName}-${index}-option-label`,
        ".MuiButtonBase-root"
      ).should("have.class", "Mui-checked");
    });
  }
  if (data.toggleIndex?.length) {
    data.toggleIndex.forEach((index) => {
      cy.getBySel(`${data.fieldName}-${index}-field`).find("input").click();
    });
  }
};
