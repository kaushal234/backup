import _ from "lodash";
import moment from "moment/moment";

const masterSalesForecastArrayFactory = (values: any) => {
  const masterSalesForecastArray = [];
  if (values.synchronized) {
    const salesForecasts: Array<any> = [];
    values.salesForecasts.forEach((salesForecast: any) => {
      salesForecasts.push({
        status: values.status,
        asm: _.get(values, "asm.value", null),
        sso: _.get(values, "sso.value", null),
        buyer: _.get(values, "buyer.value", null),
        endUser: values.endUser
          ? _.get(values, "endUser.value")
          : _.get(values, "buyer.value", null),
        country: _.get(values, "country.value", null),
        thirdParty: _.get(values, "thirdParty.value", null),
        equoteId: values.equoteId || null,
        synchronized: !!values.synchronized,
        airport: _.get(salesForecast, "airport.value", null),
        estimatedSaleDate: moment(salesForecast.estimatedSaleDate).format(),
        successPercentage: parseInt(salesForecast.successPercentage || 0, 10),
        customerSuccessPercentage: parseInt(
          salesForecast.customerSuccessPercentage || 0,
          10
        ),
        factory: _.get(salesForecast, "factory.value", null),
        product: _.get(salesForecast, "product.value", null),
        tier: _.get(salesForecast, "tier.value", null),
        quantity: parseInt(salesForecast.quantity, 10),
        comment: salesForecast.comment,
        margin: parseFloat(salesForecast.margin),
        price: parseInt(salesForecast.price, 10),
      });
    });
    masterSalesForecastArray.push({ salesForecasts });
  } else {
    values.salesForecasts.forEach((salesForecast: any) => {
      if (salesForecast.submitted) {
        return;
      }
      masterSalesForecastArray.push({
        salesForecasts: [
          {
            status: values.status,
            asm: _.get(values, "asm.value", null),
            sso: _.get(values, "sso.value", null),
            buyer: _.get(values, "buyer.value", null),
            endUser: values.endUser
              ? _.get(values, "endUser.value")
              : _.get(values, "buyer.value", null),
            country: _.get(values, "country.value", null),
            thirdParty: _.get(values, "thirdParty.value", null),
            equoteId: values.equoteId || null,
            synchronized: !!values.synchronized,
            airport: _.get(salesForecast, "airport.value", null),
            estimatedSaleDate: moment(salesForecast.estimatedSaleDate).format(),
            successPercentage: parseInt(
              salesForecast.successPercentage || 0,
              10
            ),
            customerSuccessPercentage: parseInt(
              salesForecast.customerSuccessPercentage || 0,
              10
            ),
            factory: _.get(salesForecast, "factory.value", null),
            product: _.get(salesForecast, "product.value", null),
            tier: _.get(salesForecast, "tier.value", null),
            quantity: parseInt(salesForecast.quantity, 10),
            comment: salesForecast.comment,
            margin: parseFloat(salesForecast.margin),
            price: parseInt(salesForecast.price, 10),
          },
        ],
      });
    });
  }
  return masterSalesForecastArray;
};

export { masterSalesForecastArrayFactory };
