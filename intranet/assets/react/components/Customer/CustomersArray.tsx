import React from "react";
import CustomerLine from "./CustomerLine";

interface IProps {
  fields: any;
  form: any;
}

const CustomersArray = (props: IProps) => {
  const { fields, form } = props;
  return fields.map((customer: any, index: number) => {
    return (
      <CustomerLine
        key={index}
        name={`${customer}`}
        customer={fields.get(index)}
        customerIndex={index}
        form={form}
      />
    );
  });
};

export default CustomersArray;
