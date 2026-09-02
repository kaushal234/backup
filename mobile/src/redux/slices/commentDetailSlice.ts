/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IComment } from "../../@type/IGetAllCommentResponse";

interface CommentDetailState {
  comments: Array<IComment> | null;
  refreshCounter: number;
}

const initialState: CommentDetailState = {
  comments: null,
  refreshCounter: 0,
};

export const commentDetailSlice = createSlice({
  name: "commentDetail",
  initialState,
  reducers: {
    setComments: (state, action: PayloadAction<Array<IComment> | null>) => {
      state.comments = action.payload;
    },
    refreshCommentData: (state) => {
      state.refreshCounter += 1;
    },
  },
});

export const { setComments, refreshCommentData } = commentDetailSlice.actions;

export default commentDetailSlice.reducer;
