import _ from "lodash";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.type) {
    errors.type = "Type is mandatory.";
  }

  if (!values.shortDescription) {
    errors.shortDescription = "Short description is mandatory.";
  }

  if (!values.description) {
    errors.description = "Description is mandatory.";
  }

  if (
    values.submit &&
    (!values.productTypes ||
      (values.productTypes && values.productTypes.length === 0)) &&
    (!values.competitors ||
      (values.competitors && values.competitors.length === 0)) &&
    (!values.customers ||
      (values.customers && values.customers.length === 0)) &&
    (!values.suppliers ||
      (values.suppliers && values.suppliers.length === 0)) &&
    values.type !== undefined &&
    values.type.label !== "Miscellaneous Information"
  ) {
    errors._error =
      "One of Customer, Competitor, Supplier or Product Type should not be null.";
  }

  if (values.submit && values.formType === "add") {
    const hasDivision = Object.values(values.divisions ?? {}).includes(true);
    if (!hasDivision) {
      const divisionError = "At least one division to notify is mandatory.";
      const formError = _.get(errors, "_error");
      _.set(
        errors,
        "_error",
        formError ? `${formError} ${divisionError}` : divisionError
      );
    }
  }

  if (values.shortDescription && values.shortDescription.length > 75) {
    errors.shortDescription =
      "Short description should be shorter than 75 characters.";
  }
  const competitorPricing = _.get(values, "competitorPricing", {});

  const competitorPricingErrors: any = {};
  if (!_.isEmpty(competitorPricing)) {
    if (!competitorPricing.quotationDate) {
      competitorPricingErrors.quotationDate =
        "Quotation date is mandatory for competitor pricing.";
    }
    if (!competitorPricing.competitor) {
      competitorPricingErrors.competitor =
        "Competitor is mandatory for competitor pricing.";
    }
    if (!competitorPricing.price) {
      competitorPricingErrors.price =
        "Price is mandatory for competitor pricing.";
    }
    if (!competitorPricing.termsOfDelivery) {
      competitorPricingErrors.termsOfDelivery =
        "Terms of delivery is mandatory for competitor pricing.";
    }
    if (
      !competitorPricing.quantity ||
      (competitorPricing.quantity && competitorPricing.quantity < 1)
    ) {
      competitorPricingErrors.quantity =
        "Quantity date is mandatory for competitor pricing and must be superior than 0.";
    }
    if (!competitorPricing.model) {
      competitorPricingErrors.model =
        "Model date is mandatory for competitor pricing.";
    }
    if (!competitorPricing.currency) {
      competitorPricingErrors.currency =
        "Currency date is mandatory for competitor pricing.";
    }
    if (!_.isEmpty(competitorPricingErrors)) {
      errors.competitorPricing = competitorPricingErrors;
    }
  }
  return errors;
};

export default validate;
