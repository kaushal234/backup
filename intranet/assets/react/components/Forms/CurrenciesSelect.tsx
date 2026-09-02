import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import { getCurrenciesMapping } from "../../selectors/currency/currencySelectors";
import { renderSelect } from "./Elements";
import { RootState } from "../../store";

interface IProps {
  name: any;
  component: any;
  label?: any;
  required?: any;
  blankChoice?: any;
  currencies: any;
}

function CurrenciesSelect(props: IProps) {
  const {
    required = true,
    name = "currency",
    label = "Currency",
    blankChoice = true,
    component = renderSelect,
    currencies,
  } = props;
  return (
    <Field name={name} component={component} label={label} required={required}>
      {blankChoice && <option />}
      {currencies.map((currency: any) => (
        <option value={currency.value} key={currency.value}>
          {currency.label}
        </option>
      ))}
    </Field>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    currencies: getCurrenciesMapping(state),
  };
};

export default connect(mapStateToProps)(CurrenciesSelect);
