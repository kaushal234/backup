/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { DRAWER_ITEMS } from "../../constants/drawerItems";
import { IDrawerMenuItem } from "../../@type/IDrawerMenuItem";

interface AppBarState {
  selectedItem: IDrawerMenuItem;
  menuItems: Array<IDrawerMenuItem>;
  isDrawerOpen?: boolean;
}

const initialState: AppBarState = {
  selectedItem: DRAWER_ITEMS[0],
  menuItems: DRAWER_ITEMS,
  isDrawerOpen: false,
};

export const appBarSlice = createSlice({
  name: "appBar",
  initialState,
  reducers: {
    setSelectedDrawerItem: (state, action: PayloadAction<IDrawerMenuItem>) => {
      state.selectedItem = action.payload;
    },
    setResetDrawerItem: (state) => {
      const [defaultItem] = DRAWER_ITEMS;
      state.selectedItem = defaultItem;
    },
    setIsDrawerOpen: (state, action: PayloadAction<boolean>) => {
      state.isDrawerOpen = action.payload;
    },
  },
});

export const { setSelectedDrawerItem, setResetDrawerItem, setIsDrawerOpen } =
  appBarSlice.actions;

export default appBarSlice.reducer;
