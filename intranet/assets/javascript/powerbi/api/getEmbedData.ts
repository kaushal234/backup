import axios from "axios";
import { POWER_BI_URL } from "../constants";

export const getEmbedData = async (reportId: string, workspaceId?: string) => {
  const jwt = window.user?.token || null;
  return axios.get(`${POWER_BI_URL}/powerbi/getEmbedToken`, {
    params: {
      reportId,
      ...(workspaceId ? { workspaceId } : {}),
    },
    headers: {
      Authorization: `Bearer ${jwt}`,
      Accept: "application/json",
    },
  });
};
