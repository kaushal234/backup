import React from "react";
import { connect } from "react-redux";
import { getProductFamiliesMapping } from "../../selectors/catalogue/productFamilySelector";
import { fetchProductFamilies as fetchProductFamiliesAction } from "../../actions/catalogue/productFamilyActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  productFamilies: any;
  productFamiliesListIsLoading: any;
  fetchProductFamilies: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
  onChange?: any;
}

function ProductFamilyAsyncSelect(props: IProps) {
  const {
    productFamilies,
    productFamiliesListIsLoading,
    fetchProductFamilies,
    required = false,
    name = "productFamily",
    label = "Product Family",
    placeholder = "Type to search",
  } = props;
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={productFamilies}
      isLoadingExternally={productFamiliesListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 2) {
          return;
        }
        fetchProductFamilies(input);
      }}
      {...props}
      name={name}
      label={label}
      placeholder={placeholder}
      required={required}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { productFamily } = state;
  return {
    productFamilies: getProductFamiliesMapping(state),
    productFamiliesListIsLoading: productFamily.productFamiliesListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchProductFamilies: (search: any) => {
      dispatch(fetchProductFamiliesAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(ProductFamilyAsyncSelect);
