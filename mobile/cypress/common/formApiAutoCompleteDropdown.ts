import { assertTextMatch } from "../utils/utils";

export const formApiAutoCompleteDropdown = (data: {
  fieldName: string;
  checkLabel?: string;
  checkValue?: string | RegExp;
  clear?: boolean;
  requiredError?: string;
  selectValue?: string;
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
      .findByIconIdLike("CloseIcon")
      .click({ force: true });
  }
  if (data.requiredError) {
    cy.getBySel(`${data.fieldName}-select`).find("input").click();
    cy.get("body").click(0, 0);
    cy.getBySel(`${data.fieldName}-select`)
      .find(".Mui-error")
      .contains(data.requiredError);
  }
  if (data.selectValue) {
    cy.getBySel(`${data.fieldName}-select`)
      .find("input")
      .type(data.selectValue);
    cy.getBySel(`${data.fieldName}-select-item-0`).click();
  }
};
