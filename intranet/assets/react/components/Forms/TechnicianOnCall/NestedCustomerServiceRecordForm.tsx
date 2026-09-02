import React from "react";
import Translator from "bazinga-translator";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";
import { fetchPeople } from "../../../utils/dropdown/people";

function NestedCustomerServiceRecordForm() {
  return (
    <>
      <GenericFormComponent
        type="SingleSelectAutoCompleteDropdown"
        fetchList={fetchPeople}
        label={Translator.trans("intervention.fields.leader")}
        name="nestedCustomerServiceRecord.leader"
      />
      <GenericFormComponent
        type="DatePicker"
        label={Translator.trans("csr.fields.planned_date")}
        name="nestedCustomerServiceRecord.plannedAt"
      />
    </>
  );
}

export default NestedCustomerServiceRecordForm;
