import React from "react";
import { connect } from "react-redux";
import { getBusinessPartnersMapping } from "../../selectors/erp/erpSelectors";
import {
  fetchSageSuppliers as fetchSageSuppliersAction,
  fetchBusinessPartners as fetchBusinessPartnersAction,
} from "../../actions/erp/businessPartnersActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { IDropdownItem } from "../../types/IDropdownItem";

interface IProps {
  businessPartners: any;
  required: any;
  name: any;
  label: any;
  fetchBusinessPartners: any;
  filter: any;
  onChange?: (value: IDropdownItem | Array<IDropdownItem>) => void;
  businessPartnersListIsLoading?: boolean;
  isClearable?: boolean;
  fetchSageSuppliers?: any;
  isMulti?: boolean;
}

function BusinessPartnersSelect({
  businessPartners,
  required = true,
  name = "businessPartner",
  label = "Business Partner",
  fetchBusinessPartners,
  fetchSageSuppliers,
  businessPartnersListIsLoading,
  filter,
  isClearable,
  isMulti,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      list={businessPartners}
      name={name}
      label={label}
      placeholder="Search by number or name"
      required={required}
      isLoadingExternally={businessPartnersListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }

        if (filter !== "sage") {
          fetchBusinessPartners(input, filter);
        } else {
          fetchSageSuppliers(input);
        }
      }}
      isClearable={isClearable}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { erp } = state;
  return {
    businessPartners: getBusinessPartnersMapping(state),
    businessPartnersListIsLoading: erp.businessPartnersListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchBusinessPartners: (search: any, filter: any) => {
      dispatch(fetchBusinessPartnersAction(search, filter));
    },
    fetchSageSuppliers: (search: any) => {
      dispatch(fetchSageSuppliersAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(BusinessPartnersSelect);
