import _ from "lodash";

const marketIntelligenceSubscriptionFactory = (values: any) => {
  if (values.all) {
    return {};
  }

  return {
    type: _.get(values, "type.value", null),
    competitor: _.get(values, "competitor.value", null),
    customer: _.get(values, "customer.value", null),
    productType: _.get(values, "productType.value", null),
    supplier: _.get(values, "supplier.name", null),
  };
};

export { marketIntelligenceSubscriptionFactory };
