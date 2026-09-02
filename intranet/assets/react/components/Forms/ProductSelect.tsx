import React from "react";
import { connect } from "react-redux";
import { fetchProducts as fetchProductsAction } from "../../actions/catalogue/productsActions";
import { getProductsMapping } from "../../selectors/catalogue/productSelector";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  products: any;
  productsListIsLoading: any;
  fetchProducts: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
  onChange?: any;
}

function ProductSelect(props: IProps) {
  const {
    products,
    productsListIsLoading,
    fetchProducts,
    required = false,
    name = "product",
    label = "Product",
    placeholder = "Type to search",
  } = props;
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={products}
      isLoadingExternally={productsListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 2) {
          return;
        }
        fetchProducts(input);
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
  const { product } = state;
  return {
    products: getProductsMapping(state),
    productsListIsLoading: product.productsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchProducts: (search: any) => {
      dispatch(fetchProductsAction(search));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(ProductSelect);
