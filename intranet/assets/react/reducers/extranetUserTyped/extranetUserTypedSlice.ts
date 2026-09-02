/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { RootState } from "../../store";
import { IExtranetUser as IDetailedExtranetUser } from "../../types/IGetExtranetUserByIdResponse";
import { IGetAllExtranetUsersResponse as IExtranetUserCollectionItem } from "../../types/IGetAllExtranetUsersResponse";

export interface IExtranetUserTypedSlice {
  extranetUsers: IExtranetUsersList;
}
interface IExtranetUsersList {
  [key: string]: IDetailedExtranetUser | IExtranetUserCollectionItem;
}

const initialState: IExtranetUserTypedSlice = {
  extranetUsers: {},
};

export const extranetUserTypedSlice = createSlice({
  name: "extranetUserTyped",
  initialState,
  reducers: {
    addExtranetUser: (state, action: PayloadAction<IDetailedExtranetUser>) => {
      const iri = action.payload["@id"];
      state.extranetUsers[iri] = action.payload;
    },
    addExtranetUsers: (
      state,
      action: PayloadAction<Array<IExtranetUserCollectionItem>>
    ) => {
      action.payload.forEach((extranetUser: IExtranetUserCollectionItem) => {
        const iri = extranetUser["@id"];
        state.extranetUsers[iri] = extranetUser;
      });
    },
  },
});

export const { addExtranetUser, addExtranetUsers } =
  extranetUserTypedSlice.actions;

export const selectExtranetUsers = (state: RootState) =>
  state.extranetUserTyped.extranetUsers;

export const selectExtranetUserByIri = (iri: string) => (state: RootState) =>
  state.extranetUserTyped.extranetUsers[iri];

export default extranetUserTypedSlice.reducer;
