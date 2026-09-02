import { NavigateFunction } from "react-router";
import { AppDispatch } from "../redux/store";

export interface ISetupUserParams {
  token: string;
  dispatch: AppDispatch;
  navigate: NavigateFunction;
}
