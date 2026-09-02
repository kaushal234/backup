import _ from "lodash";

const marketIntelligenceFactory = (values: any) => {
  const competitors: Array<any> = [];
  const customers: Array<any> = [];
  const productTypes: Array<any> = [];
  const suppliers: Array<any> = [];
  const divisions = [];

  if (values.competitors) {
    values.competitors.forEach((competitor: any) => {
      competitors.push(competitor.value);
    });
  }

  if (values.customers) {
    values.customers.forEach((customer: any) => {
      customers.push(customer.value);
    });
  }

  if (values.productTypes) {
    values.productTypes.forEach((productType: any) => {
      productTypes.push(productType.value);
    });
  }

  if (values.suppliers) {
    values.suppliers.forEach((supplier: any) => {
      suppliers.push(supplier.name);
    });
  }

  if (values.divisions) {
    Object.entries(values.divisions).forEach(([division, value]) => {
      if (value) {
        divisions.push(division);
      }
    });
  }
  divisions.push("/divisions/5");

  return {
    id: values.id,
    type: values.type.value,
    shortDescription: values.shortDescription,
    description: values.description,
    url: values.url,
    positionLevels: values.positionLevels
      ? values.positionLevels.split(" ")
      : [],
    competitors,
    customers,
    productTypes,
    suppliers,
    divisions,
  };
};

export { marketIntelligenceFactory };

const marketIntelligenceEditFormFactory = (marketIntelligence: any) => {
  const {
    id,
    type,
    shortDescription,
    description,
    url,
    positionLevels,
    competitors,
    customers,
    productTypes,
    suppliers,
    divisions,
  } = marketIntelligence;

  let formDivision: Array<any> = [];
  (divisions || []).forEach((division: any) => {
    const key = division["@id"];
    formDivision = { ...formDivision, [key]: true };
  });

  return {
    id,
    type: { value: _.get(type, "@id"), label: _.get(type, "name") },
    shortDescription,
    description,
    url,
    competitors: (competitors || []).map(({ name, ...competitor }: any) => ({
      value: _.get(competitor, "@id"),
      label: name,
    })),
    customers: (customers || []).map(({ name, ...customer }: any) => ({
      value: _.get(customer, "@id"),
      label: name,
    })),
    productTypes: (productTypes || []).map(
      ({ englishName, ...productType }: any) => ({
        value: _.get(productType, "@id"),
        label: englishName,
      })
    ),
    suppliers: (suppliers || []).map((supplier: any) => ({
      value: supplier,
      label: supplier,
      name: supplier,
    })),
    divisions: formDivision,
    positionLevels: positionLevels.join(" "),
  };
};

export { marketIntelligenceEditFormFactory };
