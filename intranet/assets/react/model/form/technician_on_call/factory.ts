import _ from "lodash";
import Translator from "bazinga-translator";
import moment from "moment/moment";
import { buildOptionLabel } from "../../../selectors/extranetUser/extranetUserSelectors";
import { IFACTOR_OPTIONS } from "../../../constants/constants";

const technicianOnCallWriteFactory = (
  values: any,
  confidentialChanged?: boolean
) => {
  const {
    id,
    originalTitle,
    originalDescription,
    equipmentRecord,
    assignee,
    technician,
    airport,
    errorCodes,
    unitOperationalStatus,
    technicianOnCallType,
    serviceActivity,
    salesOrganisationService,
    indiceFactor,
    tags,
    nestedCustomerServiceRecord,
    customer,
    hourMeter,
    thirdPartyName,
    thirdPartyRef,
    mainContact,
    contacts,
    serialNumber,
    confidential,
  } = values;
  let submittedToc: any = {
    originalTitle,
    originalDescription,
    equipmentRecord: _.get(equipmentRecord, "value", null),
    serialNumber,
    assignee: _.get(assignee, "value", null),
    technician: _.get(technician, "value", null),
    airport: _.get(airport, "value", null),
    errorCodes,
    unitOperationalStatus: _.get(unitOperationalStatus, "value", null),
    technicianOnCallType: _.get(technicianOnCallType, "value", null),
    serviceActivity: _.get(serviceActivity, "value", null),
    indiceFactor: _.get(indiceFactor, "value", null),
    hourMeter: parseInt(hourMeter, 10),
    salesOrganisationService: _.get(salesOrganisationService, "value", null),
    tags: tags && tags.length ? tags.map((tag: any) => tag.value) : [],
    thirdPartyName: thirdPartyName?.trim() ? thirdPartyName.trim() : null,
    thirdPartyRef,
    mainContact: _.get(mainContact, "value", null),
    contacts:
      contacts && contacts.length
        ? contacts.map((contact: any) => contact.value)
        : [],
    customer: _.get(customer, "value", null),
    confidential,
  };

  if (nestedCustomerServiceRecord) {
    submittedToc = {
      ...submittedToc,
      nestedCustomerServiceRecord: {
        leader: _.get(nestedCustomerServiceRecord, "leader.value", null),
        plannedAt: moment(nestedCustomerServiceRecord.plannedAt).isValid()
          ? moment(nestedCustomerServiceRecord.plannedAt).format(
              "YYYY-MM-DDTHH:mm:ssZ"
            )
          : null,
      },
    };
  }
  if (id) {
    submittedToc = {
      "@id": `/service/technician_on_calls/${id}`,
      id,
      ...submittedToc,
    };
  }

  const reason = (values.confidentialReason || "").trim();
  if (confidentialChanged && reason.length > 0) {
    submittedToc = {
      ...submittedToc,
      reason,
    };
  }

  return submittedToc;
};

const technicianOnCallFormFactory = (values: any) => {
  const {
    id,
    title,
    originalTitle,
    description,
    originalDescription,
    equipmentRecord,
    assignee,
    technician,
    airport,
    errorCodes,
    unitOperationalStatus,
    technicianOnCallType,
    serviceActivity,
    salesOrganisationService,
    indiceFactor,
    tags,
    customer,
    thirdPartyName,
    thirdPartyRef,
    mainContact,
    contacts,
    serialNumber,
    confidential,
  } = values;

  return {
    id,
    originalTitle: originalTitle || title,
    originalDescription: originalDescription || description,
    equipmentRecord: equipmentRecord
      ? {
          value: _.get(equipmentRecord, "@id"),
          label: equipmentRecord.serialNumber,
          hourMeter: equipmentRecord.hourMeter,
          salesOrganisationService: equipmentRecord.salesOrganisationService,
        }
      : null,
    serialNumber,
    assignee: assignee
      ? {
          value: _.get(assignee, "@id"),
          label: `${assignee.lastname}, ${assignee.firstname}`,
        }
      : null,
    technician: technician
      ? {
          value: _.get(technician, "@id"),
          label: `${technician.lastname}, ${technician.firstname}`,
        }
      : null,
    airport: airport
      ? {
          value: _.get(airport, "@id"),
          label: `${airport.code} - ${airport.cityName}`,
        }
      : null,
    errorCodes,
    unitOperationalStatus: unitOperationalStatus
      ? {
          value: _.get(unitOperationalStatus, "@id", null),
          label: unitOperationalStatus.name,
        }
      : null,
    technicianOnCallType: technicianOnCallType
      ? {
          value: _.get(technicianOnCallType, "@id"),
          label: Translator.trans(technicianOnCallType.name),
        }
      : null,
    serviceActivity: serviceActivity
      ? { value: _.get(serviceActivity, "@id"), label: serviceActivity.name }
      : null,
    salesOrganisationService: salesOrganisationService
      ? {
          value: _.get(salesOrganisationService, "@id"),
          label: salesOrganisationService.name,
        }
      : null,
    indiceFactor:
      IFACTOR_OPTIONS.find((option) => option.value === indiceFactor) ||
      IFACTOR_OPTIONS[1],
    hourMeter: equipmentRecord?.hourMeter ?? null,
    tags:
      tags && tags.length
        ? tags.map((tag: any) => ({
            value: tag["@id"],
            label: Translator.trans(tag.name),
          }))
        : [],
    mainContact: mainContact
      ? {
          value: _.get(mainContact, "@id"),
          label: buildOptionLabel(mainContact),
        }
      : null,
    contacts:
      contacts && contacts.length
        ? contacts
            .filter((contact: any) => !contact.disabled)
            .map((contact: any) => ({
              value: contact["@id"],
              label: buildOptionLabel(contact),
            }))
        : [],
    thirdPartyName,
    thirdPartyRef,
    customer: customer
      ? {
          value: _.get(customer, "@id"),
          label: customer.name,
        }
      : null,
    confidential,
  };
};

const duplicateTechnicianOnCallFactory = (values: any) => ({
  inputLines: values.technicianOnCallClones?.map(
    ({
      id,
      airport,
      salesOrganisationService,
      hourMeter,
      nestedCsrRequested,
      nestedCustomerServiceRecord,
    }: any) => {
      let plannedAt = null;
      if (
        nestedCustomerServiceRecord?.plannedAt &&
        moment(nestedCustomerServiceRecord.plannedAt).isValid()
      ) {
        plannedAt = moment(nestedCustomerServiceRecord.plannedAt).format(
          "YYYY-MM-DDTHH:mm:ssZ"
        );
      }

      return {
        equipmentRecord: `/equipment_records/${id}`,
        airport: _.get(airport, "value"),
        salesOrganisationService: _.get(salesOrganisationService, "value"),
        hourMeter:
          hourMeter !== null && hourMeter !== undefined && hourMeter !== ""
            ? parseInt(hourMeter, 10)
            : null,
        ...(nestedCsrRequested && {
          nestedCustomerServiceRecord: {
            leader: nestedCustomerServiceRecord?.leader?.value ?? null,
            plannedAt,
          },
        }),
      };
    }
  ),
});

export {
  technicianOnCallWriteFactory,
  technicianOnCallFormFactory,
  duplicateTechnicianOnCallFactory,
};
