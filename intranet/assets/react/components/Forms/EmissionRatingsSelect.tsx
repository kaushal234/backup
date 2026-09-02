import React from "react";
import { connect } from "react-redux";
import { getEmissionRatingsMapping } from "../../selectors/emissionRating/emissionRatingSelectors";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  emissionRatingsList: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
}

function EmissionRatingsSelect({
  emissionRatingsList,
  required = true,
  name = "tiers",
  label = "Emission Rating",
  placeholder = "Search by name",
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={emissionRatingsList}
      name={name}
      placeholder={placeholder}
      label={label}
      required={required}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    emissionRatingsList: getEmissionRatingsMapping(state),
  };
};

export default connect(mapStateToProps)(EmissionRatingsSelect);
