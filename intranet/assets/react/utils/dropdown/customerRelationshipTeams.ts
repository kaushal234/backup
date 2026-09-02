import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllCustomerRelationshipTeams } from "../../api/getAllCustomerRelationshipTeams";

interface ILocation {
  name?: string | null;
}

export const createCustomerRelationshipTeamDropdownItem = (item: {
  "@id": string;
  id: number | null;
  customerBusinessPartnerCode: string | null;
  erpLocation?: ILocation | null;
}): IDropdownItem => {
  return {
    label: `CRT#${item.id} ${
      item.customerBusinessPartnerCode || item.erpLocation?.name
        ? `(${item.erpLocation?.name ?? "-"} / ${
            item.customerBusinessPartnerCode ?? "-"
          })`
        : ""
    }`,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllCustomerRelationshipTeams = async (
  customerIri?: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllCustomerRelationshipTeams({ customerIri });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCustomerRelationshipTeamDropdownItem(item)
  );
};
