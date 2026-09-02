import { API_FETCH_ER, API_UPDATE_ER } from "../constants";

export function fetchEquipmentRecordsBySerialNumber(
  input: any,
  // eslint-disable-next-line default-param-last
  groups: Array<any> = [],
  groupsOverride?: any,
  type = API_FETCH_ER
) {
  const cleanedInput = input.replace(/[ ;,]+/g, ",");
  const serialNumbers = cleanedInput.split(",").filter(Boolean);
  const serialNumbersQuery =
    serialNumbers.length > 1
      ? serialNumbers
          .map((serialNumber: any) => `serialNumber[]=${serialNumber}`)
          .join("&")
      : `serialNumber=${serialNumbers[0]}`;

  const serializationGroupsQuery = groups.length
    ? groups
        .map(
          (group) =>
            `normalization_groups${
              groupsOverride ? "_override" : ""
            }[]=${group}`
        )
        .join("&")
    : "";

  const url = `/equipment_records?${serialNumbersQuery}${
    groups.length ? `&` : ``
  }${serializationGroupsQuery}`;

  return {
    type,
    payload: {
      request: {
        url,
      },
    },
  };
}

export function fetchEquipmentRecords(serialNumber?: any) {
  const serialNumberQuery = serialNumber ? `?serialNumber=${serialNumber}` : "";
  return {
    type: API_FETCH_ER,
    payload: {
      request: {
        url: `/equipment_records${serialNumberQuery}`,
      },
    },
  };
}

export function fetchEquipmentRecordsByProducts(products: any) {
  const params = new URLSearchParams(
    products.map((product: any) => ["product", product])
  );
  return {
    type: API_FETCH_ER,
    payload: {
      request: {
        url: `/equipment_records?${params.toString()}`,
      },
    },
  };
}

export function updateEquipmentRecord(
  equipmentRecord: any,
  // eslint-disable-next-line default-param-last
  url: any = equipmentRecord["@id"],
  index: any
) {
  return {
    type: API_UPDATE_ER,
    payload: {
      request: {
        url,
        body: equipmentRecord,
      },
    },
    index,
  };
}
