import React from "react";
import { connect } from "react-redux";
import {
  fetchItems as fetchItemsAction,
  fetchSageItems as fetchSageItemsAction,
} from "../../actions/erp/itemsAction";
import { getPartsListMapping } from "../../selectors/erp/erpSelectors";
import { ERP_FETCH_ITEMS } from "../../constants";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  parts: any;
  partsListIsLoading: any;
  fetchItems: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  erp: any;
  onChange: any;
  fetchSageItems?: any;
}

function PartItemSelect({
  parts,
  partsListIsLoading,
  fetchItems,
  fetchSageItems,
  erp,
  onChange,
  required = false,
  label = "Part Number",
  name = "partNumber",
  placeholder = "Type to search",
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      name={name}
      label={label}
      required={required}
      list={parts}
      placeholder={placeholder}
      isLoadingExternally={partsListIsLoading}
      onChange={onChange}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }

        erp !== 390 ? fetchItems(input, erp) : fetchSageItems(input);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { erp } = state;
  return {
    parts: getPartsListMapping(state),
    partsListIsLoading: erp.partsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchSageItems: (search: any) => {
      dispatch(fetchSageItemsAction(search, ERP_FETCH_ITEMS));
    },
    fetchItems: (search: any, erp: any) => {
      dispatch(fetchItemsAction(search, erp, ERP_FETCH_ITEMS));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(PartItemSelect);
