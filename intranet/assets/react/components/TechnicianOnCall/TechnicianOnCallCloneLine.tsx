import React, { useState } from "react";
import { connect } from "react-redux";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchServiceOrganisationOptions } from "../../utils/dropdown/serviceOrganisation";
import { fetchPeople } from "../../utils/dropdown/people";
import "../../../stylesheets/service/technicianOnCall.module.scss";

interface IProps {
  lineEquipmentRecordPath: any;
  fields: any;
  index: any;
  canCreateCsr: any;
}

function TechnicianOnCallCloneLine({
  lineEquipmentRecordPath,
  fields,
  index,
  canCreateCsr,
}: IProps) {
  const [nestedCsrRequested, setNestedCsrRequested] = useState(false);
  const equipmentRecordLine = fields.get(index);

  return (
    <tr className="technician_on_call_clone_line__wrapper">
      <td className="cell">
        <p className="text-start">{equipmentRecordLine?.serialNumber}</p>
      </td>
      <td className="cell">
        <GenericFormComponent
          type="SingleSelectAutoCompleteDropdown"
          name={`${lineEquipmentRecordPath}.airport`}
          fetchList={fetchAirport}
        />
      </td>
      <td className="cell">
        <GenericFormComponent
          type="SingleSelectDynamicDropdown"
          name={`${lineEquipmentRecordPath}.salesOrganisationService`}
          fetchList={fetchServiceOrganisationOptions}
        />
      </td>
      <td className="cell">
        <GenericFormComponent
          type="Field"
          allowNumbersOnly
          name={`${lineEquipmentRecordPath}.hourMeter`}
        />
      </td>
      {canCreateCsr && (
        <>
          <td className="cell form-check form-switch">
            <GenericFormComponent
              type="Switch"
              name={`${lineEquipmentRecordPath}.nestedCsrRequested`}
              onChange={() => {
                setNestedCsrRequested(!nestedCsrRequested);
              }}
              fitContent
            />
          </td>
          {nestedCsrRequested && (
            <>
              <td className="cell">
                <GenericFormComponent
                  type="SingleSelectAutoCompleteDropdown"
                  fetchList={fetchPeople}
                  name={`${lineEquipmentRecordPath}.nestedCustomerServiceRecord.leader`}
                />
              </td>
              <td className="cell">
                <GenericFormComponent
                  type="DatePicker"
                  name={`${lineEquipmentRecordPath}.nestedCustomerServiceRecord.plannedAt`}
                />
              </td>
            </>
          )}
        </>
      )}
    </tr>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    canCreateCsr: state.user.canCreateCsr,
  };
};
export default connect(mapStateToProps)(TechnicianOnCallCloneLine);
