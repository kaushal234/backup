import { createAsyncThunk } from "@reduxjs/toolkit";
import { fetchTechnicianOnCall } from "../api/getTechnicianOnCallById";
import { ITechnicianOnCall } from "../types/IGetTechnicalOnCallsResponse";
import { IThunkRootState } from "../types/IThunkRootState";

interface IfetchTechnicianOnCallThunkParams {
  tocId: string;
}
export const fetchTechnicianOnCallThunk = createAsyncThunk<
  ITechnicianOnCall | null,
  IfetchTechnicianOnCallThunkParams,
  IThunkRootState
>("fetchTechnicianOnCallThunk", async (params) => {
  const response = await fetchTechnicianOnCall(params);
  return response.data ?? null;
});
