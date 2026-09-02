import _ from "lodash";

const validate = (values: any) => {
  const errors: any = {};
  if (!values.status) {
    errors.status = "You must select a status.";
  }

  if (!values.asm) {
    errors.asm = `You must select an ASM.`;
  }

  if (!values.sso) {
    errors.sso = `You must select a SSO.`;
  }

  if (!values.buyer) {
    errors.buyer = `You must select a buyer.`;
  }

  if (!values.country) {
    errors.country = `You must select a country.`;
  }

  const salesForecasts = _.get(values, "salesForecasts", []);
  if (values.submit && salesForecasts.length < 1) {
    errors._error = "At least one sales forecast line must be entered";
  } else {
    const salesForcastsLineArrayErrors: Array<any> = [];

    salesForecasts.forEach((salesForecast: any, index: any) => {
      const salesForecastLineErrors: any = {};
      if (!salesForecast.estimatedSaleDate) {
        salesForecastLineErrors.estimatedSaleDate = "This date must be set.";
      }

      if (salesForecast.successPercentage === null) {
        salesForecastLineErrors.successPercentage = `An Alvest success percentage must be entered.`;
      }

      if (salesForecast.customerSuccessPercentage === null) {
        salesForecastLineErrors.customerSuccessPercentage = `A customer success percentage must be entered.`;
      }

      if (!salesForecast.factory) {
        salesForecastLineErrors.factory = `You must select a factory.`;
      }

      if (!salesForecast.tier) {
        salesForecastLineErrors.tier = `You must select an emission rating.`;
      }

      if (!salesForecast.product) {
        salesForecastLineErrors.product = `You must select a product.`;
      }

      if (!salesForecast.quantity) {
        salesForecastLineErrors.quantity = `You must set a quantity.`;
      }

      if (!salesForecast.comment || salesForecast.comment.length <= 0) {
        salesForecastLineErrors.comment = `A comment must be entered`;
      }

      if (!_.isEmpty(salesForecastLineErrors)) {
        salesForcastsLineArrayErrors[index] = salesForecastLineErrors;
      }
    });

    if (salesForcastsLineArrayErrors.length) {
      errors.salesForecasts = salesForcastsLineArrayErrors;
    }
  }
  return errors;
};

export default validate;
