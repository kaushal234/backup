import { useEffect, useState } from "react";
import { IDropdownItem } from "../types/IDropdownItem";
import { IGroupedDropdownItem } from "../types/IGroupedDropdownItem";

interface IProps {
  list: Array<IDropdownItem> | Array<IGroupedDropdownItem>;
}

const stringifyDropDownItemList = (list: Array<IDropdownItem>) => {
  return list.map((item) => ({
    ...item,
    label: String(item.label),
    value: String(item.value),
    ...(item.tooltip && { tooltip: String(item.tooltip) }),
  }));
};

const stringifyGroupedDropdownItemList = (
  list: Array<IGroupedDropdownItem>
) => {
  return list.map((item) => ({
    ...item,
    label: String(item.label),
    options: stringifyDropDownItemList(item.options),
  }));
};

export const useSortedList = (props: IProps) => {
  const { list } = props;

  const [sortedList, setSortedList] = useState<
    Array<IDropdownItem> | Array<IGroupedDropdownItem>
  >([]);

  const sortList = (options: Array<IDropdownItem>) => {
    const newOptions = [...options];
    newOptions.sort((a, b) => a.label.localeCompare(b.label));
    return newOptions;
  };

  const sortGroupedList = (options: Array<IGroupedDropdownItem>) => {
    const newGroupedOptions = [...options];
    for (let i = 0; i < newGroupedOptions.length; i++) {
      const newOptions = [...newGroupedOptions[i].options];
      newOptions.sort((a, b) => a.label.localeCompare(b.label));
      newGroupedOptions[i].options = newOptions;
    }
    newGroupedOptions.sort((a, b) => a.label.localeCompare(b.label));
    return newGroupedOptions;
  };

  const isGroupedDropdownItemArray = (
    options: Array<IDropdownItem | IGroupedDropdownItem>
  ): options is Array<IGroupedDropdownItem> => {
    return options.length > 0 && "options" in options[0];
  };

  useEffect(() => {
    if (list.length) {
      if (isGroupedDropdownItemArray(list)) {
        setSortedList(sortGroupedList(stringifyGroupedDropdownItemList(list)));
      } else {
        setSortedList(sortList(stringifyDropDownItemList(list)));
      }
    }
  }, [JSON.stringify(list)]);

  return sortedList;
};
