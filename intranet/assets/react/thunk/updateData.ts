import { createAsyncThunk } from "@reduxjs/toolkit";
import { fetchData } from "../api/fetchData";
import { RootState } from "../store";

export const updateData = createAsyncThunk<
  { data: number }, // return type
  number, // param type
  { state: RootState }
>("updateData", async (value) => {
  // call API function, format data if needed
  const response = await fetchData(value);
  return response;
});
