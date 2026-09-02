import React from "react";
import ProductManufacturingLine from "./ProductManufacturingLine";

interface IProps {
  fields: any;
  factory: any;
  year: any;
}

const ProductManufacturingArray = (props: IProps) => {
  const { fields, factory, year } = props;
  return fields.map((product: any, index: number) => {
    return (
      <ProductManufacturingLine
        key={index}
        index={index}
        productPath={product}
        factory={factory}
        year={year}
        product={fields.get(index)}
      />
    );
  });
};

export default ProductManufacturingArray;
