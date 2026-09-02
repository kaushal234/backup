import React from "react";
import ProductLine from "./ProductLine";

interface IProps {
  fields: any;
}

const ProductsArray = (props: IProps) => {
  const { fields } = props;
  return fields.map((product: any, index: number) => {
    return (
      <ProductLine
        key={index}
        name={`${product}`}
        product={fields.get(index)}
      />
    );
  });
};

export default ProductsArray;
