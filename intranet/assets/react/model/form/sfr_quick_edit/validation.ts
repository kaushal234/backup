import moment from "moment";
import _ from "lodash";

const validate = (values: any, props: any) => {
  const errors: any = {};

  const dirtySalesForecasts = (values.salesForecasts || []).filter(
    (value: any, key: number) => {
      return !_.isEqual(value, props.initialValues.salesForecasts[key]);
    }
  );

  dirtySalesForecasts.forEach((salesForecast: any) => {
    if (!salesForecast.comment) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        comment: "Comment is mandatory for SFR update",
      };
    }

    if (!salesForecast.estimatedSaleDate) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        estimatedSaleDate: "Estimated Sale is mandatory",
      };
    }

    if (moment().startOf("month") > moment(salesForecast.estimatedSaleDate)) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        estimatedSaleDate: "Estimated Sale Date can't be in the past",
      };
    }

    if (!salesForecast.quantity) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        quantity: "Quantity is mandatory",
      };
    }

    if (
      !salesForecast.customerSuccessPercentage &&
      salesForecast.customerSuccessPercentage !== 0
    ) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        customerSuccessPercentage: "Customer percentage is mandatory",
      };
    }

    if (
      !salesForecast.successPercentage &&
      salesForecast.successPercentage !== 0
    ) {
      errors[salesForecast.index] = {
        ...errors[salesForecast.index],
        successPercentage: "TLD percentage is mandatory",
      };
    }
  });

  return { salesForecasts: errors };
};

export default validate;
