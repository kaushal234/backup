import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetQrCodeForEquipmentRecordResponse } from "../types/IGetQrCodeForEquipmentRecordResponse";
import { handleError } from "../utils/utils";

interface IGetQrCodeForEquipmentRecordApiPayload {
  id: string;
}

export const getQrCodeForEquipmentRecord = async (
  data: IGetQrCodeForEquipmentRecordApiPayload
): Promise<IApiResponse<IGetQrCodeForEquipmentRecordResponse>> => {
  try {
    const response = await client.get(
      `/equipment_records/${data.id}/extranet_qrcode`,
      { responseType: "blob" }
    );

    const dataUrl = URL.createObjectURL(
      new Blob([response.data], { type: "image/png" })
    );

    return {
      status: 200,
      data: dataUrl,
    };
  } catch (error) {
    return handleError(error);
  }
};
