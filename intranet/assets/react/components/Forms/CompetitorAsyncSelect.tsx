import React from "react";
import { connect } from "react-redux";
import { fetchCompetitors as fetchCompetitorsAction } from "../../actions/competitors/competitorsActions";
import { getCompetitorsMapping } from "../../selectors/competitor/competitorSelect";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  competitors: any;
  competitorsListIsLoading: any;
  fetchCompetitors: any;
  label: any;
  required?: any;
  name: any;
  placeholder: any;
  onChange?: any;
  disabled?: any;
  isMulti?: any;
}

function CompetitorAsyncSelect(props: IProps) {
  const {
    required = false,
    label = "Competitor",
    name = "competitor",
    placeholder = "Type to search",
    competitors,
    competitorsListIsLoading,
    fetchCompetitors,
    onChange,
    isMulti,
  } = props;

  return (
    <GenericFormComponent
      type={
        isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
      }
      list={competitors}
      isLoadingExternally={competitorsListIsLoading}
      {...props}
      name={name}
      label={label}
      required={required}
      placeholder={placeholder}
      onChange={onChange}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchCompetitors(input);
      }}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { competitor } = state;
  return {
    competitors: getCompetitorsMapping(state),
    competitorsListIsLoading: competitor.competitorsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchCompetitors: (search: any) => {
      dispatch(fetchCompetitorsAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(CompetitorAsyncSelect);
