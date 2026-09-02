export interface ITocSprFormData {
  deliveryAddress: string | IDeliveryAddress;
  deliveryNotes: string;
  parts: Array<IPart>;
}

export interface IPart {
  partNumber: string;
  description: string;
  quantity: number;
  unitOfMeasure: string;
  comment: string;
}

export interface IDeliveryAddress {
  contact: string | null;
  firstname: string;
  lastname: string;
  company: string;
  airport: string | null;
  phone: string;
  address: IAddress;
}
interface IAddress {
  street1: string;
  street2: string;
  postalCode: string;
  town: string;
  city: string;
  state: string;
  country: string;
}
