import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (values.technicianOnCallClones?.length > 0) {
    errors.technicianOnCallClones = [];

    values.technicianOnCallClones.forEach((tocClone: any, index: number) => {
      const lineErrors: any = {};

      if (!tocClone.airport) {
        lineErrors.airport = Translator.trans("toc.messages.errors.airport");
      }

      if (!tocClone.salesOrganisationService) {
        lineErrors.salesOrganisationService = Translator.trans(
          "toc.messages.errors.sales_organisation_service"
        );
      }

      if (
        tocClone.hourMeter !== undefined &&
        values.selectedEquipmentRecords &&
        values.selectedEquipmentRecords[index] &&
        tocClone.hourMeter < values.selectedEquipmentRecords[index].hourMeter
      ) {
        lineErrors.hourMeter = Translator.trans(
          "toc.messages.errors.hour_meter_min_value",
          {
            minValue: values.selectedEquipmentRecords[index].hourMeter,
          }
        );
      }

      errors.technicianOnCallClones[index] = lineErrors;
    });
  }

  return errors;
};

export default validate;
