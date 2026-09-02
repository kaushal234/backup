import React from "react";
import { connect } from "react-redux";
import { getAirportsMapping } from "../../selectors/apc/airportSelector";
import { fetchAirports as fetchAirportsAction } from "../../actions/apc/airportsActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  airports: any;
  airportsListIsLoading: any;
  fetchAirports: any;
  label?: any;
  required?: any;
  name: any;
  placeholder?: any;
  onChange?: any;
}

function AirportSelect({
  airports,
  airportsListIsLoading,
  fetchAirports,
  onChange,
  required = false,
  label = "Airport",
  name = "Airport",
  placeholder = "Type to search",
}: IProps) {
  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      name={name}
      label={label}
      required={required}
      list={airports}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={airportsListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchAirports(input);
      }}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { apc } = state;
  return {
    airports: getAirportsMapping(state),
    airportsListIsLoading: apc.airportsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchAirports: (search: any) => {
      dispatch(fetchAirportsAction(search));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(AirportSelect);
