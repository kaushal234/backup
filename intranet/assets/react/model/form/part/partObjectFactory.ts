import _ from "lodash";

const partObjectFactory = (values: any, object: any, module: any) => {
  const partsArray: Array<any> = [];
  values.parts.forEach((part: any) => {
    let partObject: any = {
      "@id": _.get(part, "@id", null),
      id: _.get(part, "id", null),
      partNumber: _.get(part, "partNumber.item", null),
      description: _.get(part, "partNumber.itemDescription", null),
      quantity: parseFloat(part.quantity),
      unitOfMeasure: part.unitOfMeasure,
    };

    if (module === "NCR") {
      partObject = {
        ...partObject,
        reference: part.reference,
        referenceNumber: part.referenceNumber,
        serialNumber: part.serialNumber,
      };
    }
    if (module === "VWC") {
      partObject = {
        ...partObject,
        serialNumber: part.serialNumber,
        vendorSerialNumber: part.vendorSerialNumber,
        vendorPartNumber: part.vendorPartNumber,
        failureType: part.failureType,
        failureSystem: part.failureSystem,
        ship: part.ship,
        receivedQuantity: parseInt(part.receivedQuantity, 10),
      };
    }
    partsArray.push(partObject);
  });

  return {
    "@id": _.get(object, "@id"),
    parts: partsArray,
  };
};

export { partObjectFactory };

const intialPartObjectFactory = (partObject: any, module: any) => {
  const { parts } = partObject;
  if ((parts || []).length === 0) {
    return {
      parts: [{ module }],
    };
  }
  return {
    parts: (parts || []).map(
      ({
        id,
        partNumber,
        unitOfMeasure,
        quantity,
        description,
        reference,
        referenceNumber,
        serialNumber,
        vendorSerialNumber,
        vendorPartNumber,
        standardCost,
        failureType,
        failureSystem,
        ship,
        receivedQuantity,
        ...part
      }: any) => ({
        "@id": _.get(part, "@id"),
        id,
        partNumber: {
          value: _.get(part, "@id"),
          label: `${partNumber} - ${description || "undefined"}`,
          item: partNumber,
          itemDescription: description,
        },
        quantity,
        unitOfMeasure,
        description,
        reference,
        referenceNumber,
        serialNumber,
        vendorSerialNumber,
        vendorPartNumber,
        standardCost,
        failureType,
        failureSystem,
        ship,
        receivedQuantity,
        module,
      })
    ),
  };
};

export { intialPartObjectFactory };

const initialPartsObjectFactory = (parts: any) => {
  if (parts.length === 0) {
    return [{}];
  }

  return parts.map((part: any) => {
    let formPart = {
      partNumber: {
        value: part.itemCode,
        label: `${part.itemCode} - ${part.description}`,
        itemCode: part.itemCode,
        description: part.description,
        error: part.error,
      },
      quantity: part.quantity,
      unitOfMeasure: part.unitOfMeasure,
      comment: part.comment,
    };

    if (part.partNumber) {
      formPart = {
        ...formPart,
        partNumber: {
          ...formPart.partNumber,
          error: part.partNumber.error,
        },
      };
    }

    return formPart;
  });
};

export { initialPartsObjectFactory };
