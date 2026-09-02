import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllExtranetUsersRelatedToEr } from "../../api/getAllExtranetUsersRelatedToEr";

export interface IErExtranetUserAcl {
  extranetUserGroup?: IExtranetUserGroup;
}

export interface IExtranetUserGroup {
  name?: string;
}

export interface IErExtranetUserData {
  "@id": string;
  firstname: string | null;
  lastname: string | null;
  email: string;
  username: string | null;
  asBuyer?: boolean;
  asUser?: boolean;
  asMaintainer?: boolean;
  extranetUserAcls?: Array<IErExtranetUserAcl>;
}

export const createErExtranetUserDropdownItem = (
  item: IErExtranetUserData
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
  return {
    label: `${item.firstname} ${item.lastname} - ${
      item.email || item.username
    } (${userType.join("/")})`,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllExtranetUsersRelatedToEr = async (
  equipmentRecordIri: string,
  searchText?: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllExtranetUsersRelatedToEr({
    equipmentRecordIri,
    searchText,
  });
  return (
    (response.data?.["hydra:member"] as Array<IErExtranetUserData>) ?? []
  ).map((item) => createErExtranetUserDropdownItem(item));
};
