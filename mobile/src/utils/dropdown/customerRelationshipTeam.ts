import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllCustomerRelationshipTeam } from "../../api/getAllCustomerRelationshipTeam";

interface ILocation {
  name?: string | null;
}

export const createCustomerRelationshipTeamDropdownItem = (item: {
  "@id": string;
  id: number;
  customerBusinessPartnerCode?: string | null;
  erpLocation?: ILocation | null;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `CRT#${item.id} ${
      item.customerBusinessPartnerCode || item.erpLocation?.name
        ? `(${item.erpLocation?.name ?? "-"} / ${
            item.customerBusinessPartnerCode ?? "-"
          })`
        : ""
    }`,
  };
};

export const fetchCustomerRelationshipTeam = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllCustomerRelationshipTeam({
    customer: value,
  });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCustomerRelationshipTeamDropdownItem({
      ...item,
    })
  );
};
