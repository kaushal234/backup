import qs from "qs";
import { client } from "../store";
import { handleApiError } from "../utils/api";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IHydraCollection } from "../types/IHydraCollection";
import { IEquipmentRecord } from "../types/IDropdownItem";

export interface IEquipmentRecordAutocompleteItem extends IEquipmentRecord {
  serialNumber: string;
  type?: string | null;
  model?: string | null;
}

interface IGetEquipmentRecordAutocompleteApiParams {
  autocomplete: string;
  normalization_groups: Array<"equipment_record:service">;
}

export const getEquipmentRecordAutocomplete = async (
  autocomplete: string
): Promise<
  IBasicApiResponse<IHydraCollection<IEquipmentRecordAutocompleteItem>>
> => {
  try {
    const queryParams: IGetEquipmentRecordAutocompleteApiParams = {
      autocomplete,
      normalization_groups: ["equipment_record:service"],
    };
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/equipment_records?${queryString}`);
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IHydraCollection<IEquipmentRecordAutocompleteItem>>(
      error
    );
  }
};
