import _ from "lodash";

const customerRelationshipTeamFactory = (values: any) => {
  let customerRelationshipTeam: any = {
    customer: _.get(values.customer, "value", null),
    erpLocation: _.get(values.erpLocation, "value", null),
    partsLocation: _.get(values.partsLocation, "value", null),
    serviceLocation: _.get(values.serviceLocation, "value", null),
    salesRepresentative: _.get(values.salesRepresentative, "value", null),
    partsRepresentative: _.get(values.partsRepresentative, "value", null),
    serviceRepresentative: _.get(values.serviceRepresentative, "value", null),
    customerBusinessPartnerCode: _.get(
      values.customerBusinessPartnerCode,
      "value",
      null
    ),
  };

  if (values.id) {
    customerRelationshipTeam = {
      id: values.id,
      ...customerRelationshipTeam,
    };
  }

  return customerRelationshipTeam;
};

export { customerRelationshipTeamFactory };

const customerRelationshipTeamFactoryForm = (
  customerRelationshipTeam: any,
  formType: any
) => {
  const {
    id,
    customer,
    erpLocation,
    serviceLocation,
    partsLocation,
    salesRepresentative,
    serviceRepresentative,
    partsRepresentative,
    customerBusinessPartnerCode,
  } = customerRelationshipTeam;
  let crt: any = {
    customer: { value: _.get(customer, "@id"), label: _.get(customer, "name") },
    erpLocation: erpLocation
      ? {
          value: _.get(erpLocation, "@id"),
          erp: _.get(erpLocation, "erp"),
          label: _.get(erpLocation, "name"),
        }
      : null,
    serviceLocation: serviceLocation
      ? {
          value: _.get(serviceLocation, "@id"),
          erp: _.get(serviceLocation, "erp"),
          label: _.get(serviceLocation, "name"),
        }
      : null,
    partsLocation: partsLocation
      ? {
          value: _.get(partsLocation, "@id"),
          erp: _.get(partsLocation, "erp"),
          label: _.get(partsLocation, "name"),
        }
      : null,
    salesRepresentative: salesRepresentative
      ? {
          value: _.get(salesRepresentative, "@id"),
          label: `${_.get(salesRepresentative, "lastname")} ${_.get(
            salesRepresentative,
            "firstname"
          )}`,
        }
      : null,
    serviceRepresentative: serviceRepresentative
      ? {
          value: _.get(serviceRepresentative, "@id"),
          label: `${_.get(serviceRepresentative, "lastname")} ${_.get(
            serviceRepresentative,
            "firstname"
          )}`,
        }
      : null,
    partsRepresentative: partsRepresentative
      ? {
          value: _.get(partsRepresentative, "@id"),
          label: `${_.get(partsRepresentative, "lastname")} ${_.get(
            partsRepresentative,
            "firstname"
          )}`,
        }
      : null,
    customerBusinessPartnerCode: {
      value: customerBusinessPartnerCode,
      label: customerBusinessPartnerCode,
    },
  };

  if (formType === "edition") {
    crt = {
      ...crt,
      id,
    };
  }

  return crt;
};

export { customerRelationshipTeamFactoryForm };
