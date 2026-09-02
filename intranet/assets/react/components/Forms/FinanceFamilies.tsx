import React from "react";
import { connect } from "react-redux";
import { getFinanceFamiliesMapping } from "../../selectors/finance/financeFamilySelector";
import { RootState } from "../../store";
import { IDropdownItem } from "../../types/IDropdownItem";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  financeFamilies: any;
  onChange?: (value: IDropdownItem) => void;
  name: string;
}

function FinanceFamiliesSelect({ financeFamilies, ...props }: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={financeFamilies}
      placeholder="Search by name"
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    financeFamilies: getFinanceFamiliesMapping(state),
  };
};

export default connect(mapStateToProps)(FinanceFamiliesSelect);
