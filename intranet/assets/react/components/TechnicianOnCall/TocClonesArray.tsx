import React, { useState } from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { capitalize } from "lodash";
import TechnicianOnCallDuplicateLine from "./TechnicianOnCallCloneLine";
import "../../../stylesheets/service/technicianOnCall.module.scss";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { fetchEquipmentRecordSerialNo } from "../../utils/dropdown/equipmentRecordSerialNumber";
import { IDropdownItem } from "../../types/IDropdownItem";

interface IProps {
  fields: any;
  canCreateCsr: any;
}

function TocClonesArray({ fields, canCreateCsr }: IProps) {
  const [selectedEquipmentRecords, setSelectedEquipmentRecords] = useState<
    Array<any>
  >([]);
  const handleSelectEquipmentRecords = (selectedList: any) => {
    setSelectedEquipmentRecords(selectedList);
    fields.removeAll();
    selectedList.forEach((er: any) => {
      fields.push({
        id: er.id,
        serialNumber: er.serialNumber,
        airport: er.airport
          ? {
              value: er.airport["@id"],
              label: `${er.airport.code} - ${er.airport.cityName}`,
            }
          : null,
        salesOrganisationService: er.salesOrganisationService
          ? {
              value: er.salesOrganisationService["@id"],
              label: er.salesOrganisationService.name,
            }
          : null,
        hourMeter: er.hourMeter,
      });
    });
  };

  return (
    <>
      <GenericFormComponent
        type="MutliSelectAutoCompleteDropdown"
        label={Translator.trans(
          "service.equipment_record.fields.serial_number"
        )}
        name="selectedEquipmentRecords"
        fetchList={fetchEquipmentRecordSerialNo}
        onChange={(list: Array<IDropdownItem>) => {
          handleSelectEquipmentRecords(list.map((item) => item.data?.data));
        }}
        required
      />
      {fields.length > 0 && (
        <table
          className={`w-100 toc_duplicate_table${
            canCreateCsr ? " with_csr_columns" : ""
          }`}
        >
          <thead className="toc_table_header">
            <tr>
              {[
                capitalize(
                  Translator.trans(
                    "service.equipment_record.fields.serial_number"
                  )
                ),
                Translator.trans("toc.fields.airport"),
                Translator.trans("toc.fields.sales_organisation_service"),
                capitalize(
                  Translator.trans("service.follow_up_report.fields.hourmeter")
                ),
              ].map((column) => (
                <th key={`${column}-header`}>{column}</th>
              ))}
              {canCreateCsr &&
                [
                  Translator.trans("toc.title.csr"),
                  Translator.trans("intervention.fields.leader"),
                  Translator.trans("csr.fields.planned_date"),
                ].map((column) => <th key={`${column}-header`}>{column}</th>)}
            </tr>
          </thead>
          <tbody key={fields.length}>
            {fields.map((lineEquipmentRecordPath: any, index: any) => (
              <TechnicianOnCallDuplicateLine
                key={`${lineEquipmentRecordPath}-line`}
                index={index}
                lineEquipmentRecordPath={lineEquipmentRecordPath}
                fields={fields}
                minHourmeter={selectedEquipmentRecords[index]?.hourMeter ?? 0}
              />
            ))}
          </tbody>
        </table>
      )}
    </>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    canCreateCsr: state.user.canCreateCsr,
  };
};
export default connect(mapStateToProps)(TocClonesArray);
