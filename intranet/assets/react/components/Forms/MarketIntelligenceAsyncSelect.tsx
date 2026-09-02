import React from "react";
import { connect } from "react-redux";
import { fetchMarketIntelligences as fetchMarketIntelligencesAction } from "../../actions/marketIntelligence/marketIntelligencesActions";
import { getMarketIntelligencesMapping } from "../../selectors/marketIntelligence/marketIntelligenceSelect";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  marketIntelligences: any;
  marketIntelligencesListIsLoading: any;
  fetchMarketIntelligences: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
}

function MarketIntelligenceAsyncSelect(props: IProps) {
  const {
    marketIntelligences,
    marketIntelligencesListIsLoading,
    fetchMarketIntelligences,
    onChange,
    required = false,
    label = "market Intelligence",
    name = "maketIntelligence",
    placeholder = "Type to search",
  } = props;

  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={marketIntelligences}
      isLoadingExternally={marketIntelligencesListIsLoading}
      {...props}
      name={name}
      label={label}
      required={required}
      placeholder={placeholder}
      onChange={onChange}
      onInputChange={(input: any) => {
        if (!input) {
          return;
        }
        fetchMarketIntelligences(input);
      }}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { marketIntelligence } = state;
  return {
    marketIntelligences: getMarketIntelligencesMapping(state),
    marketIntelligencesListIsLoading:
      marketIntelligence.marketIntelligencesListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchMarketIntelligences: (search: any) => {
      dispatch(fetchMarketIntelligencesAction(search));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(MarketIntelligenceAsyncSelect);
