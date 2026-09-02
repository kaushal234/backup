import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getFactoriesMapping } from "../../selectors/location/factoriesSelector";
import { RootState } from "../../store";
import { IFactory } from "../../types/ISupplierCorrectiveActionRequestFactoryProps";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface FactoriesSelectProps {
  factories: Array<IFactory>;
  name: string;
  required: boolean;
}

function FactoriesSelect({
  factories,
  name = "factory",
  required = true,
  ...props
}: FactoriesSelectProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={factories}
      name={name}
      placeholder="Search by name"
      {...props}
      label={Translator.trans("location_areas.fields.factory")}
      required={required}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    factories: getFactoriesMapping(state),
  };
};

export default connect(mapStateToProps)(FactoriesSelect);
