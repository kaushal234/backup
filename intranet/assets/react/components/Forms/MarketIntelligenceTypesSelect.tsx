import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getMarketIntelligenceTypesMapping } from "../../selectors/marketIntelligence/marketIntelligenceTypeSelect";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  marketIntelligenceTypeList: any;
  required: any;
  name: any;
  placeholder: any;
  disabled?: boolean;
  onChange?: any;
}

function MarketIntelligenceTypesSelect({
  required = true,
  name = "type",
  placeholder = "Search by name",
  marketIntelligenceTypeList,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      list={marketIntelligenceTypeList}
      name={name}
      placeholder={placeholder}
      label={Translator.trans("market_intelligence.fields.type")}
      required={required}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    marketIntelligenceTypeList: getMarketIntelligenceTypesMapping(state),
  };
};

export default connect(mapStateToProps)(MarketIntelligenceTypesSelect);
