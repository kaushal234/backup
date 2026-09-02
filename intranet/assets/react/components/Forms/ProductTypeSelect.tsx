import React from "react";
import { connect } from "react-redux";
import { getProductTypesMapping } from "../../selectors/catalogue/productTypeSelector";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  productTypesList: any;
  productTypeList?: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
  isDisabled?: any;
  isMulti?: any;
}

function ProductTypeSelect({
  required = false,
  name = "productType",
  label = "Product Type",
  placeholder = "Select a Product type",
  productTypesList,
  isMulti,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      list={productTypesList}
      name={name}
      label={label}
      required={required}
      placeholder={placeholder}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    productTypesList: getProductTypesMapping(state),
  };
};

export default connect(mapStateToProps)(ProductTypeSelect);
