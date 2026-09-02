import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { fetchNonConformitiesList as fetchNonConformitiesListAction } from "../../actions/nonConformity/nonConformityActions";
import { getNonConformityListMapping } from "../../selectors/nonConformity/nonConformitySelectors";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  nonConformities: any;
  nonConformitiesListIsLoading: any;
  fetchNonConformitiesList: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
}

function NonConformityAsyncSelect({
  nonConformities,
  nonConformitiesListIsLoading,
  fetchNonConformitiesList,
  onChange,
  required = false,
  label = Translator.trans("home.quick_search.people_label"),
  name = "non_conformity",
  placeholder = Translator.trans("home.quick_search.placeholder"),
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      name={name}
      label={label}
      required={required}
      list={nonConformities}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={nonConformitiesListIsLoading}
      onInputChange={(input: any) => {
        fetchNonConformitiesList(input);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { nonConformity } = state;
  return {
    nonConformities: getNonConformityListMapping(state),
    nonConformitiesListIsLoading: nonConformity.nonConformitiesListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchNonConformitiesList: (search: any) => {
      dispatch(fetchNonConformitiesListAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(NonConformityAsyncSelect);
