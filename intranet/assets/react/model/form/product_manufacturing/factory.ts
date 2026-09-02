import moment from "moment";

const productManufacturingFactory = (values: any, year: any) => {
  const newProductManufacturings: Array<any> = [];
  const { productManufacturings } = values;

  Object.keys(productManufacturings || []).forEach((index) => {
    if (
      !productManufacturings[index].factoryStandardEfficiency ||
      !productManufacturings[index].industrialIncorporationParameter
    ) {
      return;
    }
    const dashIndex = index.indexOf("-");
    let newPricing: any = {
      factoryStandardEfficiency: parseInt(
        productManufacturings[index].factoryStandardEfficiency,
        10
      ),
      industrialIncorporationParameter: parseFloat(
        productManufacturings[index].industrialIncorporationParameter
      ),
      modelBaseHours: parseInt(productManufacturings[index].modelBaseHours, 10),
      factory: index.substr(0, dashIndex),
      effectiveAt: productManufacturings[index].effectiveAt
        ? productManufacturings[index].effectiveAt
        : moment(year, "YYYY").format(),
    };
    if (values.id && Object.hasOwn(productManufacturings[index], "@id")) {
      newPricing = {
        ...newPricing,
        "@id": productManufacturings[index]["@id"],
      };
    }
    newProductManufacturings.push(newPricing);
  });

  return {
    id: values.id,
    productManufacturings: newProductManufacturings,
  };
};

export { productManufacturingFactory };
