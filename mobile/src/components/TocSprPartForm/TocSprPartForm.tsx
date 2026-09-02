import React, { useEffect } from "react";
import "./TocSprPartForm.css";
import { useTranslation } from "react-i18next";
import { Avatar, IconButton, Typography } from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import { fetchItemMonologistics } from "../../utils/dropdown/itemMonologistics";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import { getItemMonologisticByItemCode } from "../../api/getItemMonologisticByItemCode";
import { IDynamicFieldUpdate } from "../../hooks/useDynamicField";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";

const validateQuantity = (value: string) => {
  if (!value) return "toc_spr_form.part.quantity.error.required";
  if (+value < 1) return "toc_spr_form.part.quantity.error.min";
  return "";
};

export interface ITocSprPartData {
  partNumber: IDropdownItem | null;
  description: string;
  quantity: string;
  uom: string;
  comment: string;
}

interface IProps {
  onChange: (data: IDynamicFieldUpdate<ITocSprPartData>) => void;
  onDelete: () => void;
  isFormTouched: boolean;
  index: number;
}

export default function TocSprPartForm(props: IProps) {
  const { onChange, onDelete, isFormTouched, index } = props;

  const dispatch = useAppDispatch();

  const { t } = useTranslation();

  const partNumber = useFormApiAutoCompleteDropdown({
    defaultValue: null,
    fetchData: fetchItemMonologistics,
    requiredError: "toc_spr_form.part.part_number.error.required",
  });

  const quantity = useFormField({
    defaultValue: "",
    isNumber: true,
    allowDecimal: true,
    validate: validateQuantity,
  });

  const uom = useFormField({
    defaultValue: "",
    requiredError: "toc_spr_form.part.uom.error.required",
  });

  const comment = useFormField({
    defaultValue: "",
  });

  const formValidator = useFormValidator([partNumber, quantity, uom]);

  const setPartNumberRelatedFields = async () => {
    if (!partNumber.value) return;
    dispatch(showMainLoader(true));
    quantity.clear();
    uom.clear();
    const response = await getItemMonologisticByItemCode({
      itemCode: partNumber.value?.id ?? "",
    });
    if (response.data) {
      quantity.helper.setValue("1");
      uom.helper.setValue(response.data.unitOfMeasure ?? "");
    }
    dispatch(showMainLoader(false));
  };

  useEffect(() => {
    setPartNumberRelatedFields();
  }, [partNumber.value]);

  useEffect(() => {
    let description = "";
    if (partNumber.value?.additionalInfo?.type === "ItemMonologistic") {
      description = partNumber.value?.additionalInfo?.data.description;
    }
    onChange({
      data: {
        partNumber: partNumber.value,
        description,
        quantity: quantity.value,
        uom: uom.value,
        comment: comment.value,
      },
      isFormErrorFree: formValidator.isFormErrorFree,
      isSubmitDisabled: formValidator.isSubmitDisabled,
    });
  }, [
    partNumber.value,
    quantity.value,
    uom.value,
    comment.value,
    formValidator.isFormErrorFree,
    formValidator.isSubmitDisabled,
  ]);

  useEffect(() => {
    if (isFormTouched) {
      formValidator.touchAll();
    }
  }, [isFormTouched]);

  return (
    <div className="toc_spr_part_form__wrapper">
      <div className="toc_spr_part_form__header_wrapper">
        <Typography
          className="cui_light_text"
          variant="h5"
          gutterBottom
          data-cy={`toc-spr-part-form-heading-${index}`}
        >
          {t("toc_spr_form.part.title")} #{index}
        </Typography>
        {index !== 1 && (
          <Avatar className="cui_action" color="primary">
            <IconButton
              onClick={onDelete}
              data-cy={`toc-spr-part-form-delete-${index}`}
            >
              <DeleteIcon />
            </IconButton>
          </Avatar>
        )}
      </div>
      <div className="toc_parts_form__wrapper">
        <FormApiAutoCompleteDropdown
          {...partNumber.fieldProps}
          requiredLabel
          label="toc_spr_form.part.part_number.title"
          id="part-number"
          placeholder="toc_spr_form.part.part_number.placeholder"
          dataCy={`toc-spr-part-form-part-number-${index}`}
        />
        <FormField
          {...quantity.fieldProps}
          requiredLabel
          label="toc_spr_form.part.quantity.title"
          id="quantity"
          placeholder="toc_spr_form.part.quantity.placeholder"
          dataCy={`toc-spr-part-form-quantity-${index}`}
        />
        <FormField
          {...uom.fieldProps}
          requiredLabel
          label="toc_spr_form.part.uom.title"
          id="uom"
          placeholder="toc_spr_form.part.uom.placeholder"
          dataCy={`toc-spr-part-form-uom-${index}`}
        />
        <FormField
          {...comment.fieldProps}
          label="toc_spr_form.part.comment.title"
          id="comment"
          placeholder="toc_spr_form.part.comment.placeholder"
          dataCy={`toc-spr-part-form-comment-${index}`}
        />
      </div>
    </div>
  );
}
