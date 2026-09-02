import React from "react";
import { connect } from "react-redux";
import { getLocationsMapping } from "../../selectors/location/locationSelectors";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  LocationsList?: any;
  required?: boolean;
  name?: string;
  label?: string | null;
  placeholder?: string;
  locationListName?: string;
  onChange?: any;
}

function LocationSelect({
  LocationsList,
  required = true,
  name = "location",
  label = "Location",
  placeholder = "Search by name",
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={LocationsList}
      name={name}
      placeholder={placeholder}
      label={label || ""}
      required={required}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState, { locationListName }: IProps) => {
  return {
    LocationsList: getLocationsMapping(state, locationListName || "locations"),
  };
};

export default connect(mapStateToProps)(LocationSelect);
