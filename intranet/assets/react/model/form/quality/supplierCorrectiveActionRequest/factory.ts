import _ from "lodash";
import { ISupplierCorrectiveActionRequestApi } from "../../../../types/ISupplierCorrectiveActionRequestPropsApi";
import { ISupplierCorrectiveActionRequestPrefill } from "../../../../types/ISupplierCorrectiveActionRequestPrefillProps";
import { ISupplierCorrectiveActionRequestFactoryProps } from "../../../../types/ISupplierCorrectiveActionRequestFactoryProps";

const supplierCorrectiveActionRequestFactory = (
  values: ISupplierCorrectiveActionRequestFactoryProps
) => {
  return {
    id: values.id ? values.id : null,
    factory: values.factory.value,
    iFactor: values.importanceFactor,
    shortDescription: values.shortDescription,
    description: values.description,
    representative: values.representative.value,
    leader: _.get(values.leader, "value", null),
    supplierNumber: values.supplierNumber.value,
    issueOrigin: values.issueOrigin ? values.issueOrigin : null,
    correctiveAction: values.correctiveAction ? values.correctiveAction : null,
    preventiveAction: values.preventiveAction ? values.preventiveAction : null,
    commercialAgreement: values.commercialAgreement
      ? values.commercialAgreement
      : null,
    verificationDescription: values.verificationDescription
      ? values.verificationDescription
      : null,
    conclusion: values.conclusion ? values.conclusion : null,
  };
};

const supplierCorrectiveActionRequestFactoryForm = ({
  id,
  factory,
  iFactor,
  shortDescription,
  description,
  representative,
  leader,
  supplierNumber,
  supplierName,
  issueOrigin,
  correctiveAction,
  preventiveAction,
  commercialAgreement,
  verificationDescription,
  conclusion,
}: ISupplierCorrectiveActionRequestApi) => {
  return {
    id,
    factory: {
      erp: factory.erp,
      value: factory["@id"],
      label: `${factory.name} - ${factory.erp}`,
    },
    importanceFactor: iFactor,
    shortDescription,
    description,
    representative: {
      value: representative["@id"],
      label: `${representative.lastname}, ${representative.firstname} - ${representative.email}`,
    },
    leader: {
      value: leader?.["@id"],
      label: leader
        ? `${leader.lastname}, ${leader.firstname} - ${leader.email}`
        : null,
    },
    supplierNumber: {
      value: supplierNumber,
      name: supplierName,
      label: `${supplierNumber} ${supplierName}`,
    },
    issueOrigin,
    correctiveAction,
    preventiveAction,
    commercialAgreement,
    verificationDescription,
    conclusion,
  };
};

const supplierCorrectiveActionRequestPrefillFactoryForm = ({
  factory,
  shortDescription,
  description,
}: ISupplierCorrectiveActionRequestPrefill) => {
  return {
    factory: factory
      ? {
          erp: factory.erp,
          value: factory["@id"],
          label: `${factory.name} - ${factory.erp}`,
        }
      : null,
    shortDescription: shortDescription || null,
    description: description || null,
  };
};

export {
  supplierCorrectiveActionRequestFactory,
  supplierCorrectiveActionRequestFactoryForm,
  supplierCorrectiveActionRequestPrefillFactoryForm,
};
