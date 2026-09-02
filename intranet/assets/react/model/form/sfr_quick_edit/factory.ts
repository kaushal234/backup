import moment from "moment";

const salesForecastFactory = (salesForecast: any) => {
  return {
    id: salesForecast.id,
    status: salesForecast.status,
    comment: salesForecast.comment,
    quantity: parseInt(salesForecast.quantity, 10),
    customerSuccessPercentage: parseInt(
      salesForecast.customerSuccessPercentage,
      10
    ),
    successPercentage: parseInt(salesForecast.successPercentage, 10),
    estimatedSaleDate: moment(salesForecast.estimatedSaleDate).format(),
    synchronized: salesForecast.synchronized || false,
    notificationRestricted: salesForecast.notificationRestricted || false,
  };
};

export default salesForecastFactory;
