import React from "react";
import { connect } from "react-redux";
import { fetchCountries as fetchCountriesAction } from "../../actions/country/countriesActions";
import { getCountriesMapping } from "../../selectors/country/countrySelector";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  countries: any;
  countriesListIsLoading: any;
  fetchCountries: any;
  required: any;
  name: any;
  label: any;
  placeholder: any;
  component: any;
}

function CountrySelect({
  required = false,
  label = "Country",
  name = "country",
  placeholder = "Type to search",
  countries,
  countriesListIsLoading,
  fetchCountries,
  ...props
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      name={name}
      label={label}
      required={required}
      list={countries}
      placeholder={placeholder}
      isLoadingExternally={countriesListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchCountries(input);
      }}
      {...props}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { country } = state;
  return {
    countries: getCountriesMapping(state),
    countriesListIsLoading: country.countriesListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchCountries: (search: any) => {
      dispatch(fetchCountriesAction(search));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(CountrySelect);
