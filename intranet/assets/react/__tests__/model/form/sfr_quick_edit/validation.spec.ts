import { describe, it } from "mocha";
import { expect } from "chai";
import validate from "../../../../model/form/sfr_quick_edit/validation";

describe("Sales Forecast validation", () => {
  const props = { initialValues: { salesForecasts: [{}] } };
  const defaultErrors = {
    comment: "Comment is mandatory for SFR update",
    estimatedSaleDate: "Estimated Sale is mandatory",
    quantity: "Quantity is mandatory",
    customerSuccessPercentage: "Customer percentage is mandatory",
    successPercentage: "TLD percentage is mandatory",
  };
  it("Should have default errors", () => {
    expect(
      validate({ salesForecasts: [{ diff: true, index: 2 }] }, props)
    ).to.deep.equal({ salesForecasts: { 2: defaultErrors } });
  });
  it("Should have no error with valid data", () => {
    const date = new Date();
    expect(
      validate(
        {
          salesForecasts: [
            {
              comment: "comment",
              estimatedSaleDate: new Date(
                date.getFullYear(),
                date.getMonth(),
                1
              ),
              quantity: 12,
              customerSuccessPercentage: 75,
              successPercentage: 50,
              index: 0,
            },
          ],
        },
        props
      )
    ).to.deep.equal({ salesForecasts: {} });
  });
});
