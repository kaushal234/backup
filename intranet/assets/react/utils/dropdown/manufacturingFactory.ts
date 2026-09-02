import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllManufacturingFactories } from "../../api/getAllManufacturingFactories";

export const createManufacturingFactoryDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllManufacturingFactories = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllManufacturingFactories();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createManufacturingFactoryDropdownItem(item)
  );
};
