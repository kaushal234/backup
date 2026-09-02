import { useEffect } from "react";
import { useAppDispatch } from "./hooks";
import {
  setResetDrawerItem,
  setSelectedDrawerItem,
} from "../redux/slices/appBarSlice";
import { DRAWER_ITEMS } from "../constants/drawerItems";

export const useDrawer = (route: string) => {
  const dispatch = useAppDispatch();

  useEffect(() => {
    const match = DRAWER_ITEMS.find((item) => item.path === route);
    dispatch(setSelectedDrawerItem(match ?? DRAWER_ITEMS[0]));
    return () => {
      dispatch(setResetDrawerItem());
    };
  }, []);
};
