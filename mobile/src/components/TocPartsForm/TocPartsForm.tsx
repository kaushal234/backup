import React, { useEffect, useState } from "react";
import "./TocPartsForm.css";
import { useTranslation } from "react-i18next";
import { Button } from "@mui/material";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { ITocPartsFormData } from "../../@type/ITocPartsFormData";
import { useFormValidator } from "../../hooks/useFormValidator";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import { REPLACEMENT_OPTIONS } from "../../constants/constants";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";

const validatePartNumberOrVendorPartNumber = (
  value: string,
  vendorPartNumber: string
) => {
  if (!value && !vendorPartNumber) {
    return "toc_parts_form.part_number.error.required";
  }
  return "";
};

const validateQuantity = (value: string) => {
  if (!value) return "toc_parts_form.quantity.error.required";
  if (+value < 1) return "toc_parts_form.quantity.error.min";
  return "";
};

interface IProps {
  onSubmit: (data: ITocPartsFormData) => void;
  partNumber?: string;
  vendorPartNumber?: string;
  description?: string;
  quantity?: string;
  comment?: string | null;
  replacement?: IDropdownItem | null;
}

export default function TocPartsForm(props: IProps) {
  const {
    partNumber: partNumberDefault = "",
    vendorPartNumber: vendorPartNumberDefault = "",
    description: descriptionDefault = "",
    quantity: quantityDefault = "",
    comment: commentDefault = "",
    replacement: replacementDefault = null,
    onSubmit,
  } = props;

  const { t } = useTranslation();

  const [vendorPartNumberValue, setVendorPartNumberValue] = useState("");

  const partNumber = useFormField({
    defaultValue: partNumberDefault,
    validate: (value) =>
      validatePartNumberOrVendorPartNumber(value, vendorPartNumberValue),
    dependsOn: [vendorPartNumberValue],
  });

  const vendorPartNumber = useFormField({
    defaultValue: vendorPartNumberDefault,
    validate: (value) =>
      validatePartNumberOrVendorPartNumber(value, partNumber.value),
    dependsOn: [partNumber.value],
  });

  const description = useFormField({
    defaultValue: descriptionDefault,
    requiredError: "toc_parts_form.description.error.required",
  });

  const quantity = useFormField({
    defaultValue: quantityDefault,
    isNumber: true,
    allowDecimal: true,
    validate: validateQuantity,
  });

  const comment = useFormField({
    defaultValue: commentDefault ?? "",
  });

  const replacement = useFormSingleSelectDropdown({
    defaultValue: replacementDefault,
    list: REPLACEMENT_OPTIONS,
  });

  const formValidator = useFormValidator([
    description,
    quantity,
    partNumber,
    vendorPartNumber,
  ]);

  const handleSubmit = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      onSubmit({
        partNumber: partNumber.value,
        vendorPartNumber: vendorPartNumber.value,
        description: description.value,
        quantity: quantity.value ? +quantity.value : 0,
        comment: comment.value,
        replacement: replacement.value?.id || null,
      });
    }
  };

  useEffect(() => {
    setVendorPartNumberValue(vendorPartNumber.value);
  }, [vendorPartNumber.value]);

  return (
    <div className="toc_parts_form__wrapper">
      <FormField
        {...partNumber.fieldProps}
        label="toc_parts_form.part_number.title"
        id="part-number"
        placeholder="toc_parts_form.part_number.placeholder"
        dataCy="toc-parts-form-part-number"
      />
      <FormField
        {...vendorPartNumber.fieldProps}
        label="toc_parts_form.vendor_part_number.title"
        id="vendor-part-number"
        placeholder="toc_parts_form.vendor_part_number.placeholder"
        dataCy="toc-parts-form-vendor-part-number"
      />
      <FormField
        {...quantity.fieldProps}
        label="toc_parts_form.quantity.title"
        requiredLabel
        id="quantity"
        placeholder="toc_parts_form.quantity.placeholder"
        dataCy="toc-parts-form-quantity"
      />

      <FormSingleSelectDropdown
        {...replacement.fieldProps}
        label="toc_parts_form.replacement.title"
        placeholder="toc_parts_form.replacement.placeholder"
        dataCy="toc-parts-form-replacement"
      />
      <FormField
        {...description.fieldProps}
        label="toc_parts_form.description.title"
        id="description"
        placeholder="toc_parts_form.description.placeholder"
        dataCy="toc-parts-form-description"
        requiredLabel
        rows={4}
      />
      <FormField
        {...comment.fieldProps}
        label="toc_parts_form.comment.title"
        id="comment"
        placeholder="toc_parts_form.comment.placeholder"
        dataCy="toc-parts-form-comment"
        rows={4}
      />
      <Button
        disabled={formValidator.isSubmitDisabled}
        className="cui_button toc_parts_form__submit"
        type="submit"
        variant="contained"
        onClick={handleSubmit}
        data-cy="toc-form-submit"
      >
        {t("toc_parts_form.submit")}
      </Button>
    </div>
  );
}
