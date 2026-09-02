import { AnyAction } from "redux";
import { Reducer } from "@reduxjs/toolkit";
import { SUCCESS, FAILED, TAG_FETCH_LIST } from "../../constants";

interface ITagState {
  tags: any;
  tagsIsLoading: any;
  error: any;
}

const initialState: ITagState = {
  tags: [],
  tagsIsLoading: false,
  error: null,
};

const tagReducer: Reducer<ITagState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  switch (action.type) {
    case TAG_FETCH_LIST: {
      return {
        ...state,
        tagsIsLoading: true,
        error: null,
      };
    }
    case TAG_FETCH_LIST + SUCCESS: {
      const tags: any = {};
      action.payload.data["hydra:member"].forEach((tag: any) => {
        tags[tag["@id"]] = tag;
      });
      return {
        ...state,
        tags,
        tagsIsLoading: false,
        error: null,
      };
    }
    case TAG_FETCH_LIST + FAILED: {
      return {
        ...state,
        tagsIsLoading: false,
        error: action.payload,
      };
    }
    default: {
      return state;
    }
  }
};

export default tagReducer;
