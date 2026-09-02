import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { RootState } from "../../redux/store";
import { getAllContacts } from "../../api/getAllContacts";

export const createContactDropdownItem = (item: {
  "@id": string;
  firstname: string | null;
  lastname: string | null;
  email: string;
  username: string | null;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.firstname} ${item.lastname} - ${item.email || item.username}`,
  };
};

export const fetchContactOptions = createAsyncThunk<
  Array<IDropdownItem>,
  { customer?: string },
  { state: RootState }
>("fetchContactOptions", async ({ customer }) => {
  if (!customer) return [];
  const response = await getAllContacts({ customer });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createContactDropdownItem(item)
  );
});
