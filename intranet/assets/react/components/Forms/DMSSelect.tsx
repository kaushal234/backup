import React from "react";
import { connect } from "react-redux";
import { fetchDMS as fetchDMSAction } from "../../actions/dms/dmsActions";
import { getDMSMapping } from "../../selectors/dms/dmsSelector";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  dmsList: any;
  dmsListIsLoading: any;
  fetchDMS: any;
  label: any;
  required: any;
  name: any;
  placeholder: any;
  onChange: any;
}

function DMSSelect(props: IProps) {
  const {
    dmsList,
    dmsListIsLoading,
    fetchDMS,
    onChange,
    required = false,
    label = "DMS",
    name = "dms",
    placeholder = "Type to search a DMS",
  } = props;

  return (
    <GenericFormComponent
      type="SingleSelectStaticDropdown"
      name={name}
      label={label}
      required={required}
      list={dmsList}
      placeholder={placeholder}
      onChange={onChange}
      isLoadingExternally={dmsListIsLoading}
      onInputChange={(input: any) => {
        if (!input || input.length < 3) {
          return;
        }
        fetchDMS(input);
      }}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  const { dms } = state;
  return {
    dmsList: getDMSMapping(state),
    dmsListIsLoading: dms.dmsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchDMS: (search: any) => {
      dispatch(fetchDMSAction(search));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(DMSSelect);
