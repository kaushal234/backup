import { getAllLocation } from "../../api/getAllLocation";
import { client } from "../../store";
import { IDropdownItem } from "../../types/IDropdownItem";
import { IPaginatedFetchResult } from "../../types/IPaginatedDropdown";
import { IGetAllLocationResponse } from "../../types/IGetAllLocationResponse";

export const createServiceOrganisationDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: item.name,
  };
};

export const fetchServiceOrganisationOptions = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllLocation({ sso: true });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createServiceOrganisationDropdownItem(item)
  );
};

export const fetchServiceOrganisationPage = async (
  nextUrl?: string
): Promise<IPaginatedFetchResult> => {
  try {
    let data: IGetAllLocationResponse | undefined;
    if (nextUrl) {
      const response = await client.get(nextUrl);
      data = response.data;
    } else {
      const response = await getAllLocation({ sso: true });
      data = response.data;
    }
    return {
      items: (data?.["hydra:member"] ?? []).map(
        createServiceOrganisationDropdownItem
      ),
      nextUrl: data?.["hydra:view"]?.["hydra:next"],
    };
  } catch {
    return { items: [] };
  }
};
