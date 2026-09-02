import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getEquipmentRecordsMapping } from "../../selectors/equipmentRecord/equipmentRecordSelectors";
import { fetchEquipmentRecordsBySerialNumber } from "../../actions/equipmentRecordsActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  equipmentRecords: any;
  equipmentRecordsListIsLoading: any;
  fetchEquipmentRecords: any;
  label?: any;
  required?: any;
  name: any;
  placeholder?: any;
  onChange?: any;
  alert?: any;
  groups?: any;
  groupsOverride?: any;
  async?: any;
  component?: any;
  isMulti?: any;
  ignoreCase?: any;
}

function EquipmentRecordAsyncSelect(props: IProps) {
  const {
    equipmentRecords,
    equipmentRecordsListIsLoading,
    fetchEquipmentRecords,
    onChange,
    alert,
    required = false,
    label = "Equipment Record",
    name = "equipmentRecord",
    placeholder = "Type to search an ER",
    groups = [],
    groupsOverride = false,
    async = true,
    isMulti,
  } = props;
  const isGreenTag =
    equipmentRecords[0] !== undefined ? equipmentRecords[0].isGreenTag : false;

  return (
    <div>
      <GenericFormComponent
        type={
          isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
        }
        list={equipmentRecords}
        isLoadingExternally={equipmentRecordsListIsLoading}
        {...props}
        name={name}
        label={label}
        required={required}
        placeholder={placeholder}
        onChange={onChange}
        groups={groups}
        groupsOverride={groupsOverride}
        async={async}
        onInputChange={(input) => {
          if (!input || input.length < 3) {
            return;
          }
          fetchEquipmentRecords(input, groups, groupsOverride);
        }}
      />
      {alert === "green-tag-alert" && isGreenTag && (
        <h3 style={{ color: "red" }}>
          {Translator.trans(`crab.green_tag.gt_case`)}
        </h3>
      )}
    </div>
  );
}

const mapStateToProps = (state: RootState) => {
  const { equipmentRecord } = state;
  return {
    equipmentRecords: getEquipmentRecordsMapping(state),
    equipmentRecordsListIsLoading:
      equipmentRecord.equipmentRecordsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchEquipmentRecords: (search: any, groups: any, groupsOverride: any) => {
      dispatch(
        fetchEquipmentRecordsBySerialNumber(search, groups, groupsOverride)
      );
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(EquipmentRecordAsyncSelect);
