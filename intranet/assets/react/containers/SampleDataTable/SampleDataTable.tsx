import React from "react";
import { IDataTableFetchParams } from "../../types/IDataTableFetchRequest";
import { getAllPeople } from "../../api/getAllPeople";
import { IPeople } from "../../types/IGetAllPeopleResponse";
import DataTable from "../../components/DataTable/DataTable";
import { fetchPeople } from "../../utils/dropdown/people";
import { IGenericFilterFormData } from "../../types/IGenericFilterFormData";

export default function SampleDataTable() {
  const [data, setData] = React.useState<Array<IPeople>>([]);
  const [rowCount, setRowCount] = React.useState<number>(0);

  const handleFetchData = async (params: IDataTableFetchParams) => {
    const response = await getAllPeople({
      ...params.pagination,
      ...params.sort,
      ...params.filters,
    });
    setData(response.data?.["hydra:member"] ?? []);
    setRowCount(response.data?.["hydra:totalItems"] ?? 0);
  };

  const handleFetchDownloadData = async (params: IGenericFilterFormData) => {
    const response = await getAllPeople({
      ...params,
    });
    return (response.data?.["hydra:member"] ?? []).map((item) => [
      item.firstname,
      item.lastname,
      null,
    ]);
  };

  return (
    <div>
      <DataTable
        name="sample_data_table"
        title="Sample Data Table"
        headers={[
          { value: "First Name", minWidth: "30%", sortFieldName: "firstname" },
          {
            value: "Last Name",
            minWidth: "30%",
            sortFieldName: "lastname",
          },
          {
            value: "Email",
            minWidth: "40%",
            exportable: false,
          },
        ]}
        rows={data.map((item) => [item.firstname, item.lastname, item.email])}
        fetchDownloadData={handleFetchDownloadData}
        rowCount={rowCount}
        fetchData={handleFetchData}
        filterFields={[
          { type: "Field", name: "firstname", label: "First Name" },
          { type: "Field", name: "lastname", label: "Last Name" },
          {
            type: "SingleSelectAutoCompleteDropdown",
            name: "mentor",
            label: "Mentor",
            fetchList: fetchPeople,
          },
          {
            type: "MutliSelectAutoCompleteDropdown",
            name: "supervisor",
            label: "Supervisor",
            fetchList: fetchPeople,
          },
        ]}
        filterInitialValues={{
          firstname: "user",
        }}
      />
    </div>
  );
}
