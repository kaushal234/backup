import { AppDispatch, RootState } from "../store";

export interface IThunkRootState {
  state: RootState;
  dispatch: AppDispatch;
}
