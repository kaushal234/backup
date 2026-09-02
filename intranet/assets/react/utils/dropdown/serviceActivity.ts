import { getAllServiceActivities } from "../../api/getAllServiceActivities";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createServiceActivityDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: item.name,
  };
};

export const fetchServiceActivityOptions = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllServiceActivities();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createServiceActivityDropdownItem(item)
  );
};
