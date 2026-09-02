import moment from "moment/moment";

const competitorPricingFactory = (values: any) => {
  return {
    competitor: values.competitor,
    quotationDate: moment(values.quotationDate).format(),
    model: values.model,
    incoterms: values.incoterms,
    quantity: parseInt(values.quantity, 10),
    price: parseInt(values.price, 10),
    currency: values.currency,
  };
};

export { competitorPricingFactory };
