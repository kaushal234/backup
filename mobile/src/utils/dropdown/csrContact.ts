import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { RootState } from "../../redux/store";
import { createContactDropdownItem } from "./contact";
import { getAllCsrContacts } from "../../api/getAllCsrContacts";
import { IEquipmentRecord } from "../../@type/IGetCustomerServiceRecordResponse";

export const fetchCsrContactOptions = createAsyncThunk<
  Array<IDropdownItem>,
  IEquipmentRecord,
  { state: RootState }
>("fetchCsrContactOptions", async (data) => {
  const response = await getAllCsrContacts({
    buyer: data.buyer?.id,
    endUser: data.endUser?.id,
    maintainer: data.maintainer?.id,
  });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createContactDropdownItem(item)
  );
});
