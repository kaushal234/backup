import { API_CALL } from "../constants/api";

export type IApiCall = (typeof API_CALL)[keyof typeof API_CALL];
