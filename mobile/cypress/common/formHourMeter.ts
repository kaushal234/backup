import { formField } from "./formField";

export const formHourMeter = (data: {
  fieldName: string;
  checkLabel?: string;
  updateValue?: boolean;
}) => {
  cy.getBySel(`${data.fieldName}-sub-label`)
    .invoke("text")
    .then((text) => {
      const match = text.match(/\(Last Hours : (\d+)\)/);
      if (match) {
        const number = parseInt(match[1], 10);
        formField({
          fieldName: data.fieldName,
          checkLabel: data.checkLabel,
          ...(data.updateValue && { clear: true, setValue: `${number + 1}` }),
        });
      }
    });
};
