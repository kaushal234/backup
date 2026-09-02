import React, { useCallback, useState } from "react";
import {
  DataGrid,
  GridColDef,
  GridDataSource,
  GridRowSelectionModel,
  GridSlots,
  PropsFromSlot,
} from "@mui/x-data-grid";
import { Box, Checkbox, CircularProgress, TextField } from "@mui/material";
import { debounce } from "@mui/material/utils";
import Translator from "bazinga-translator";
import { IDropdownItem } from "../../types/IDropdownItem";
import { IExtranetUser } from "../../types/IExtranetUser";
import { toastConfirm } from "../../utils/utils";
import { fetchAllFilteredExtranetUsers } from "../../api/fetchAllFilteredExtranetUsers";
import { fetchExtranetUsers } from "../../api/fetchExtranetUsers";
import "./ContactTable.css";
import { IFetchExtranetUsersPayload } from "../../types/IFetchExtranetUsersPayload";

const CONTACT_TABLE_COLUMNS: Array<GridColDef<IExtranetUser>> = [
  {
    field: "id",
    headerName: Translator.trans("contact_campaign.fields.id"),
    flex: 0.5,
    minWidth: 80,
  },
  {
    field: "email",
    headerName: Translator.trans("contact_campaign.fields.email"),
    flex: 1.5,
    minWidth: 200,
  },
  {
    field: "extranetUserProfile.customer.name",
    headerName: Translator.trans("contact_campaign.fields.customer"),
    flex: 1,
    minWidth: 150,
    valueGetter: (value, row) => row.extranetUserProfile?.customer?.name || "-",
  },
  {
    field: "extranetUserProfile.erpLocation.name",
    headerName: Translator.trans("contact_campaign.fields.location"),
    flex: 1,
    minWidth: 150,
    valueGetter: (value, row) =>
      row.extranetUserProfile?.erpLocation?.name || "-",
  },
];

interface CustomBaseCheckboxProps
  extends PropsFromSlot<GridSlots["baseCheckbox"]> {
  selectedContacts: Array<string>;
  isSelectingAll: boolean;
  handleSelectAllFiltered: () => void;
}

function CustomBaseCheckbox({
  selectedContacts,
  isSelectingAll,
  handleSelectAllFiltered,
  ...muiProps
}: CustomBaseCheckboxProps) {
  if (muiProps.value === undefined) {
    const hasSelections = selectedContacts.length > 0;

    return (
      <Checkbox
        {...muiProps}
        checked={hasSelections}
        indeterminate={false}
        disabled={isSelectingAll}
        onClick={(e) => {
          e.stopPropagation();
          handleSelectAllFiltered();
        }}
        icon={isSelectingAll ? <CircularProgress size={20} /> : undefined}
      />
    );
  }
  return <Checkbox {...muiProps} />;
}

interface IContactTableProps {
  customerFilter?: Array<IDropdownItem>;
  locationFilter?: Array<IDropdownItem>;
  selectedContacts: Array<string>;
  onSelectedContactsChange: (ids: Array<string>) => void;
}

export default function ContactTable({
  customerFilter = [],
  locationFilter = [],
  selectedContacts,
  onSelectedContactsChange,
}: IContactTableProps) {
  const [searchQuery, setSearchQuery] = useState("");
  const [isSelectingAll, setIsSelectingAll] = useState(false);
  const debouncedSetSearchQuery = useCallback(
    debounce((query: string) => {
      setSearchQuery(query);
    }, 500),
    []
  );
  const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    debouncedSetSearchQuery(event.target.value);
  };

  const handleRowSelectionModelChange = (
    newSelection: GridRowSelectionModel
  ) => {
    if (newSelection && newSelection.ids) {
      const idsArray = Array.from(newSelection.ids);
      onSelectedContactsChange(idsArray.map((item) => `${item}`));
    }
  };

  const prepareFiltersParams = () => {
    return {
      customers:
        customerFilter.length > 0
          ? customerFilter.map((field) => field.label)
          : undefined,
      locations:
        locationFilter.length > 0
          ? locationFilter.map((field) => field.label)
          : undefined,
    };
  };

  const handleSelectAllFiltered = async () => {
    setIsSelectingAll(true);

    const { customers, locations } = prepareFiltersParams();
    const payload = {
      pagination: false,
      searchQuery,
      customers,
      locations,
    };
    const allContacts = await fetchAllFilteredExtranetUsers(payload);
    const allFilteredIds = allContacts.data?.map((contact) => contact["@id"]);
    if (allFilteredIds && allContacts.data) {
      const allFilteredAreSelected = allFilteredIds.every((id) =>
        selectedContacts.includes(id)
      );
      if (allFilteredAreSelected) {
        const newSelection = selectedContacts.filter(
          (id) => !allFilteredIds.includes(id)
        );
        onSelectedContactsChange(newSelection);
      } else {
        if (allContacts.data.length > 1000) {
          const confirmed = await toastConfirm({
            message: Translator.trans(
              "contact_campaign.message.selecting_contact",
              { contactsCount: allContacts.data.length }
            ),
          });
          if (!confirmed) {
            setIsSelectingAll(false);
            return;
          }
        }
        const newSelection = Array.from(
          new Set([...selectedContacts, ...allFilteredIds])
        );
        onSelectedContactsChange(newSelection);
      }
    }
    setIsSelectingAll(false);
  };
  const dataSource: GridDataSource = {
    getRows: async (params) => {
      const { customers, locations } = prepareFiltersParams();
      const payload: IFetchExtranetUsersPayload = {
        page: (params.paginationModel?.page ?? 0) + 1,
        pageSize: params.paginationModel?.pageSize ?? 25,
        searchQuery,
        customers,
        locations,
      };
      if (
        params.sortModel?.[0] &&
        (params.sortModel[0].field === "id" ||
          params.sortModel[0].field === "email" ||
          params.sortModel[0].field === "extranetUserProfile.customer.name" ||
          params.sortModel[0].field === "extranetUserProfile.erpLocation.name")
      ) {
        payload.sortModel = {
          field: params.sortModel[0].field ?? "id",
          sort: params.sortModel[0].sort ?? "asc",
        };
      }

      const result = await fetchExtranetUsers(payload);
      return {
        rows: result.data?.items ?? [],
        rowCount: result.data?.total ?? 0,
      };
    },
  };

  return (
    <Box>
      <Box className="mb-1">
        <TextField
          size="small"
          placeholder={Translator.trans("contact_campaign.message.search")}
          onChange={handleSearchChange}
        />
      </Box>

      <Box>
        <DataGrid
          className="contact_table__wrapper"
          columns={CONTACT_TABLE_COLUMNS}
          dataSource={dataSource}
          getRowId={(row) => row["@id"]}
          pagination
          pageSizeOptions={[10, 25, 50, 100]}
          initialState={{
            pagination: {
              paginationModel: { pageSize: 25, page: 0 },
              rowCount: 0,
            },
          }}
          sortingOrder={["asc", "desc"]}
          checkboxSelection
          disableRowSelectionOnClick
          keepNonExistentRowsSelected
          disableColumnMenu
          rowSelectionModel={{
            type: "include",
            ids: new Set(selectedContacts),
          }}
          onRowSelectionModelChange={handleRowSelectionModelChange}
          slots={{
            baseCheckbox: CustomBaseCheckbox,
          }}
          slotProps={{
            baseCheckbox: {
              selectedContacts,
              isSelectingAll,
              handleSelectAllFiltered,
            },
          }}
        />
      </Box>
    </Box>
  );
}
