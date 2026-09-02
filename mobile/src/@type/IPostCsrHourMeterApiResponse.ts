export type IPostCsrHourMeterApiResponse =
  ICustomerServiceRecordHourMeterTransaction;

interface ICustomerServiceRecordHourMeterTransaction {
  "@context": string;
  "@id": string;
  "@type": string;
  module: string;
  customerServiceRecordLegacyId: number;
  customerServiceRecord: string;
  hourMeter: number;
  createdAt: string;
  equipmentRecord: IEquipmentRecord;
  id: number;
}

export interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  serialNumber: string;
  model: string | null;
  type: string | null;
}
