import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { RootState } from "../../redux/store";
import { getAllErContacts } from "../../api/getAllErContacts";
import { IExtranetUser } from "../../@type/IGetAllContactResponse";

export const createErContactDropdownItem = (
  item: IExtranetUser
): IDropdownItem => {
  const userType = [];
  if (item.asBuyer) {
    userType.push("B");
  }
  if (item.asMaintainer) {
    userType.push("M");
  }
  if (item.asUser) {
    userType.push("U");
  }
  const userTypeString = userType.length ? `(${userType.join("/")})` : "";
  return {
    id: item["@id"],
    text: `${item.firstname} ${item.lastname} - ${
      item.email || item.username
    } ${userTypeString}`,
    additionalInfo: {
      type: "ExtranetUser",
      data: item,
    },
  };
};

export const fetchErContactOptions = createAsyncThunk<
  Array<IDropdownItem>,
  { equipmentRecord?: string },
  { state: RootState }
>("fetchErContactOptions", async ({ equipmentRecord }) => {
  if (!equipmentRecord) return [];
  const response = await getAllErContacts({ equipmentRecord });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createErContactDropdownItem(item)
  );
});
