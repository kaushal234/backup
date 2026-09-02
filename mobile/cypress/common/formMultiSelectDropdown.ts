export const formMultiSelectDropdown = (data: {
  fieldName: string;
  checkLabel?: string;
  checkValue?: string;
  clear?: boolean;
  selectIndex?: Array<number>;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }
  if (data.checkValue) {
    cy.getBySel(`${data.fieldName}-select`)
      .find("input")
      .should("have.value", data.checkValue);
  }
  if (data.clear) {
    cy.getBySel(`${data.fieldName}-select`)
      .findByIconIdLike("CancelIcon")
      .each(($icon) => {
        cy.wrap($icon).click();
      });
  }
  if (data.selectIndex?.length !== 0) {
    cy.getBySel(`${data.fieldName}-select`).click("right");
    (data.selectIndex ?? []).forEach((idx) => {
      cy.getBySel(`${data.fieldName}-select-item-${idx}`).click({
        force: true,
      });
    });
    cy.get("body").click(0, 0);
  }
};
