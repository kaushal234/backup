import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import { renderSelect } from "./Elements";
import { getTermsOfDeliveryMapping } from "../../selectors/incoterm/incotermSelectors";
import { RootState } from "../../store";

interface IProps {
  name: any;
  component: any;
  label?: any;
  required?: any;
  blankChoice?: any;
  termsOfDelivery: any;
}

function TermsOfDeliverySelect(props: IProps) {
  const {
    termsOfDelivery,
    required = true,
    name = "incoterms",
    label = "Incoterms",
    blankChoice = true,
    component = renderSelect,
  } = props;
  return (
    <Field name={name} component={component} label={label} required={required}>
      {blankChoice && <option />}
      {termsOfDelivery.map((term: any) => (
        <option value={term.value} key={term.value}>
          {term.label}
        </option>
      ))}
    </Field>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    termsOfDelivery: getTermsOfDeliveryMapping(state),
  };
};

export default connect(mapStateToProps)(TermsOfDeliverySelect);
