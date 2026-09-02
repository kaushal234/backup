const financeFamilyFactory = (values: any) => {
  const newPricings: Array<any> = [];
  const { pricings } = values;
  Object.keys(pricings || []).forEach((index) => {
    if (!pricings[index].averagePrice || !pricings[index].averageMargin) {
      return;
    }
    if (
      pricings[index].averagePrice === "" &&
      pricings[index].averageMargin === ""
    ) {
      return;
    }
    const dashIndex = index.indexOf("-");
    let newPricing: any = {
      averagePrice: parseInt(pricings[index].averagePrice, 10),
      averageMargin: parseFloat(pricings[index].averageMargin),
      sso: index.substr(0, dashIndex),
      factory: index.substr(dashIndex + 1),
    };
    if (values.id && Object.hasOwn(pricings[index], "@id")) {
      newPricing = {
        ...newPricing,
        "@id": pricings[index]["@id"],
      };
    }
    newPricings.push(newPricing);
  });
  const financeFamily: any = {
    pricings: newPricings,
    name: values.name,
    factories: values.factories,
  };

  if (values.id) {
    financeFamily.id = values.id;
  }

  return financeFamily;
};

export { financeFamilyFactory };
