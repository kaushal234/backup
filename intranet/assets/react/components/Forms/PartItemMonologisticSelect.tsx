import React from "react";
import { connect } from "react-redux";
import { fetchItemsMonologistic as fetchItemsMonologisticAction } from "../../actions/part/itemsMonologisticAction";
import { getPartsListMapping } from "../../selectors/part/itemMonologisticSelectors";
import { PART_FETCH_ITEMS_MONOLOGISTIC } from "../../constants";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  parts: any;
  partsListIsLoading: any;
  fetchItemsMonologistic: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
}

function PartItemMonologisticSelect({
  parts,
  partsListIsLoading,
  fetchItemsMonologistic,
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
        fetchItemsMonologistic(input);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    parts: getPartsListMapping(state),
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchItemsMonologistic: (search: any) => {
      dispatch(
        fetchItemsMonologisticAction(search, PART_FETCH_ITEMS_MONOLOGISTIC)
      );
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(PartItemMonologisticSelect);
