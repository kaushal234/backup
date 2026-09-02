import { assertTextMatch } from "../utils/utils";

export const formSingleSelectDropdown = (data: {
  fieldName: string;
  checkLabel?: string;
  clear?: boolean;
  requiredError?: string;
  checkValue?: string | RegExp;
  selectIndex?: number;
  selectValue?: string;
  valueError?: string;
}) => {
  if (data.checkLabel) {
    cy.getBySel(`${data.fieldName}-label`).contains(data.checkLabel);
  }
  if (data.checkValue) {
    cy.getBySel(`${data.fieldName}-select`)
      .find("input")
      .invoke("val")
      .then((value) => {
        assertTextMatch(value?.toString() ?? "", data.checkValue ?? "");
      });
  }
  if (data.clear) {
    cy.getBySel(`${data.fieldName}-select`)
      .findByIconIdLike("ClearIcon")
      .click({ force: true });
  }
  if (data.requiredError) {
    cy.getBySel(`${data.fieldName}-select`).click();
    cy.getBySel(`${data.fieldName}-select-item-0`).click();
    cy.getBySel(`${data.fieldName}-select`)
      .findByIconIdLike("ClearIcon")
      .click({ force: true });
    cy.getBySel(`${data.fieldName}-error`).contains(data.requiredError);
  }
  if (data.selectIndex !== undefined) {
    cy.getBySel(`${data.fieldName}-select`).click();
    cy.getBySel(`${data.fieldName}-select-item-${data.selectIndex}`).click();
  }
  if (data.selectValue) {
    cy.getBySel(`${data.fieldName}-select`).click();
    cy.getBySelLike(`${data.fieldName}-select-item-`).then(($items) => {
      cy.wrap($items)
        .contains(new RegExp(data.selectValue ?? "", "i"))
        .click({ force: true });
    });
  }
  if (data.valueError) {
    cy.getBySel(`${data.fieldName}-error`).contains(data.valueError);
  }
};
