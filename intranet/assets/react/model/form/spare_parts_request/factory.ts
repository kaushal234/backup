import _ from "lodash";
import { buildNameOption } from "../../../selectors/apc/airportSelector";

const tocSparePartsRequestFactory = (
  values: any,
  existingSparePartsRequest: any,
  newSparePartsRequest: any
) => {
  const partsArray: Array<any> = [];
  values.parts.forEach((part: any) => {
    partsArray.push({
      partNumber: _.get(part, "partNumber.itemCode", null),
      description: _.get(part, "partNumber.description", null),
      quantity: parseFloat(part.quantity),
      unitOfMeasure: part.unitOfMeasure,
      comment: part.comment,
    });
  });

  if (values.sph !== undefined) {
    newSparePartsRequest.sph = _.get(values.sph, "value", null);
  }

  let deliveryAddress = _.get(values, "deliveryAddress", null);
  if (!deliveryAddress) {
    deliveryAddress = {
      contact: _.get(values.contact, "value", null),
      firstname: values.firstname,
      lastname: values.lastname,
      company: values.company,
      airport: _.get(values.airport, "value", null),
      phone: values.phone,
      address: {
        street1: values.street1,
        street2: values.street2,
        postalCode: values.postalCode,
        town: values.town,
        city: values.city,
        state: values.state,
        country: _.get(values.country, "isoCode2", null),
      },
    };
  }

  let sparePartsRequestToSave = {
    tocId: _.get(existingSparePartsRequest, "technicianOnCall", null),
    deliveryAddress,
    ...newSparePartsRequest,
  };

  if (values.deliveryNotes) {
    sparePartsRequestToSave = {
      ...sparePartsRequestToSave,
      deliveryNotes: values.deliveryNotes,
    };
  }

  if (
    existingSparePartsRequest &&
    deliveryAddress === existingSparePartsRequest.deliveryAddress["@id"]
  ) {
    existingSparePartsRequest.parts.forEach((part: any) => {
      partsArray.push({
        "@id": _.get(part, "@id", null),
      });
    });

    return {
      "@id": _.get(existingSparePartsRequest, "@id", null),
      id: existingSparePartsRequest.id,
      activity: existingSparePartsRequest.activity,
      parts: partsArray,
      ...sparePartsRequestToSave,
    };
  }

  return {
    ...sparePartsRequestToSave,
    parts: partsArray,
  };
};

export { tocSparePartsRequestFactory };

const serviceBulletinSparePartsRequestFactory = (values: any) => {
  const partsArray: Array<any> = [];
  values.parts.forEach((part: any) => {
    partsArray.push({
      partNumber: _.get(part, "partNumber", null),
      description: _.get(part, "partNumber", null),
      quantity: parseInt(part.quantity, 10),
    });
  });

  const equipmentRecords: Array<any> = [];
  values.equipmentRecords.forEach((equipmentRecord: any) => {
    equipmentRecords.push(_.get(equipmentRecord, "@id", null));
  });

  let deliveryAddress = _.get(values, "deliveryAddress", null);
  if (!deliveryAddress) {
    deliveryAddress = {
      contact: _.get(values.contact, "value", null),
      airport: _.get(values.airport, "@id", null),
      firstname: values.firstname,
      lastname: values.lastname,
      company: values.company,
      phone: values.phone,
      address: {
        street1: values.street1,
        street2: values.street2,
        postalCode: values.postalCode,
        town: values.town,
        city: values.city,
        state: values.state,
        country: _.get(values.country, "isoCode2", null),
      },
    };
  }

  const newSparePartsRequest: any = {
    deliveryAddress,
    equipmentRecords,
  };

  if (values.sph !== undefined && values.sph.value) {
    newSparePartsRequest.sph = _.get(values.sph, "value", null);
  }

  if (values.sph !== undefined && values.sph["@id"]) {
    newSparePartsRequest.sph = _.get(values.sph, "@id", null);
  }

  return {
    sbId: _.get(values, "sbId", null),
    parts: partsArray,
    airport: _.get(values.airport, "@id", null),
    customer: _.get(values.customer, "@id", null),
    factory: _.get(values.factory, "@id", null),
    erpLocation: _.get(values.factory, "@id", null),
    sso: _.get(values.sso, "@id", null),
    activity: "Service Bulletin",
    type: values.type,
    ...newSparePartsRequest,
  };
};

export { serviceBulletinSparePartsRequestFactory };

const addressFactory = (values: any, sparePartsRequest: any) => {
  let deliveryAddress = _.get(values, "deliveryAddress", null);
  if (!deliveryAddress) {
    deliveryAddress = {
      contact: _.get(values.contact, "value", null),
      firstname: values.firstname,
      lastname: values.lastname,
      company: values.company,
      airport: _.get(values.airport, "value", null),
      phone: values.phone,
      address: {
        street1: values.street1,
        street2: values.street2,
        postalCode: values.postalCode,
        town: values.town,
        city: values.city,
        state: values.state,
        country: _.get(values.country, "isoCode2", null),
      },
    };
  }

  return {
    "@id": _.get(sparePartsRequest, "@id", null),
    id: sparePartsRequest.id,
    deliveryAddress,
  };
};

export { addressFactory };

const deliveryAddressFactory = (deliveryAddress: any) => {
  const { address, contact, airport, lastname, firstname, phone, company } =
    deliveryAddress;
  return {
    street1: address.street1,
    street2: address.street2,
    lastname,
    firstname,
    phone,
    company,
    postalCode: address.postalCode,
    town: address.town,
    state: address.state,
    contact: {
      value: _.get(contact, "@id"),
      label: `${_.get(contact, "lastname")} ${_.get(
        contact,
        "firstname"
      )} #${_.get(contact, "id")}`,
    },
    airport: buildNameOption(airport),
  };
};

export { deliveryAddressFactory };
