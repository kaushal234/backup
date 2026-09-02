import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.equipmentRecord && !values.serialNumber) {
    errors.equipmentRecord = Translator.trans("toc.messages.errors.serials");
  }

  if (
    values.indiceFactor === "IF 1" &&
    values.unitOperationalStatus !== null &&
    values.unitOperationalStatus.value !== "/unit_operational_statuses/MCF"
  ) {
    errors.indiceFactor = Translator.trans("toc.messages.errors.indice_factor");
  }

  if (!values.airport) {
    errors.airport = Translator.trans("toc.messages.errors.airport");
  }

  if (!values.salesOrganisationService) {
    errors.salesOrganisationService = Translator.trans(
      "toc.messages.errors.sales_organisation_service"
    );
  }

  if (!values.assignee) {
    errors.assignee = Translator.trans("toc.messages.errors.assignee");
  }

  if (!values.technicianOnCallType) {
    errors.technicianOnCallType = Translator.trans(
      "toc.messages.errors.technician_on_call_type"
    );
  }

  if (!values.serviceActivity) {
    errors.serviceActivity = Translator.trans(
      "toc.messages.errors.service_activity"
    );
  }

  if (!values.unitOperationalStatus) {
    errors.unitOperationalStatus = Translator.trans(
      "toc.messages.errors.unit_operational_status"
    );
  }

  if (!values.originalTitle) {
    errors.originalTitle = Translator.trans("toc.messages.errors.title");
  }

  if (values.originalTitle && values.originalTitle.length < 12) {
    errors.originalTitle = Translator.trans("toc.messages.errors.short");
  }

  if (!values.originalDescription) {
    errors.originalDescription = Translator.trans(
      "toc.messages.errors.description"
    );
  }

  if (values.originalDescription && values.originalDescription.length < 12) {
    errors.originalDescription = Translator.trans("toc.messages.errors.short");
  }

  if (!values.mainContact) {
    errors.mainContact = Translator.trans("toc.messages.errors.main_contact");
  }

  if (!values.customer) {
    errors.customer = Translator.trans("toc.messages.errors.customer");
  }

  if (values.equipmentRecord && !values.hourMeter) {
    errors.hourMeter = Translator.trans("toc.messages.errors.hour_meter");
  }

  if (values.hourMeter < values.equipmentRecord?.hourMeter) {
    errors.hourMeter = Translator.trans(
      "toc.messages.errors.hour_meter_min_value",
      {
        minValue: values.equipmentRecord?.hourMeter ?? 0,
      }
    );
  }

  if (values.thirdPartyRef && !values.thirdPartyName) {
    errors.thirdPartyName = Translator.trans(
      "toc.messages.errors.third_party_name_required_with_third_party_ref"
    );
  }

  return errors;
};

export default validate;
