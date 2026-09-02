import { COMMON_SET_LOADING } from "../../constants";

export function setGlobalLoader(isLoading: any) {
  return {
    type: COMMON_SET_LOADING,
    payload: {
      isLoading,
    },
  };
}
