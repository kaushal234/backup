import React, { useEffect, useState } from "react";
import "./TocSprForm.css";
import { useTranslation } from "react-i18next";
import { Avatar, Button, IconButton, Typography } from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import { useFormValidator } from "../../hooks/useFormValidator";
import TocSprPartForm, {
  ITocSprPartData,
} from "../TocSprPartForm/TocSprPartForm";
import { useDynamicField } from "../../hooks/useDynamicField";
import { useFormField } from "../../hooks/useFormField";
import { useFormApiAutoCompleteDropdown } from "../../hooks/useFormApiAutoCompleteDropdown";
import FormField from "../FormField/FormField";
import FormApiAutoCompleteDropdown from "../FormApiAutoCompleteDropdown/FormApiAutoCompleteDropdown";
import FormSingleSelectDropdown from "../FormSingleSelectDropdown/FormSingleSelectDropdown";
import { useFormSingleSelectDropdown } from "../../hooks/useFormSingleSelectDropdown";
import { SPR_ADDRESS, SPR_ADDRESS_OPTIONS } from "../../constants/constants";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchCountry } from "../../utils/dropdown/country";
import { getAllSprContacts } from "../../api/getAllSprContacts";
import { createContactDropdownItem } from "../../utils/dropdown/contact";
import FormRadioButtons from "../FormRadioButtons/FormRadioButtons";
import { useFormRadioButtons } from "../../hooks/useFormRadioButtons";
import { IRadioButton } from "../../@type/IRadioButton";
import { getAllSprAddress } from "../../api/getAllSprAddress";
import { ISparePartsRequestDeliveryAddress } from "../../@type/IGetAllSprAddressResponse";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { ITocSprFormData } from "../../@type/ITocSprFormData";

interface IProps {
  customers: Array<string>;
  onSubmit: (data: ITocSprFormData) => void;
}

const INITIAL_TOC_SPR_PART_DATA: ITocSprPartData = {
  partNumber: null,
  description: "",
  quantity: "",
  uom: "",
  comment: "",
};

const formatDate = (date: string) => {
  return new Date(date)?.toLocaleDateString("en-GB");
};

const formatAddress = (
  item: ISparePartsRequestDeliveryAddress
): IRadioButton => {
  const text = [
    `${item.contact?.lastname}, ${item.contact?.firstname} (ID#${item.contact?.id})`,
    item.address.street1,
    item.address.street2,
    `${item.address.city}, ${item.address.country}`,
    `${item.firstname} ${item.lastname} ${item.phone}`,
    `Last Used: ${formatDate(item.lastUsedAt ?? "")}`,
  ]
    .filter((currentItem) => !!currentItem)
    .join("\n");

  return {
    id: item["@id"],
    text,
  };
};

const validateAddress = (value: string, isNewAddress: boolean) => {
  if (!isNewAddress && !value) {
    return "toc_spr_form.address.delivery_address.error.required";
  }
  return "";
};

const validateLastname = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.lastname.error.required";
  }
  return "";
};

const validateFirstname = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.firstname.error.required";
  }
  return "";
};

const validateCompany = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.company.error.required";
  }
  return "";
};

const validateStreet1 = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.street_1.error.required";
  }
  return "";
};

const validateTelephone = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.telephone.error.required";
  }
  return "";
};

const validatePostalCode = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.postal_code.error.required";
  }
  return "";
};

const validateCity = (value: string, isNewAddress: boolean) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.city.error.required";
  }
  return "";
};

const validateCountry = (
  value: IDropdownItem | null,
  isNewAddress: boolean
) => {
  if (isNewAddress && !value) {
    return "toc_spr_form.address.country.error.required";
  }
  return "";
};

export default function TocSprForm(props: IProps) {
  const { customers, onSubmit } = props;

  const dispatch = useAppDispatch();
  const { t } = useTranslation();

  const [contactList, setContactList] = useState<Array<IDropdownItem>>([]);

  const [addressList, setAddressList] = useState<Array<IRadioButton>>([]);

  const parts = useDynamicField<ITocSprPartData>({
    defaultValue: [INITIAL_TOC_SPR_PART_DATA],
  });

  const type = useFormSingleSelectDropdown({
    defaultValue: SPR_ADDRESS_OPTIONS[0],
    list: SPR_ADDRESS_OPTIONS,
    requiredError: "toc_spr_form.address.type.error.required",
  });

  const isNewAddress = type.value?.id === SPR_ADDRESS.new;

  const address = useFormRadioButtons({
    list: addressList,
    validate: (value) => validateAddress(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const contact = useFormSingleSelectDropdown({
    defaultValue: null,
    list: contactList,
  });

  const airport = useFormApiAutoCompleteDropdown({
    defaultValue: null,
    fetchData: fetchAirport,
  });

  const lastname = useFormField({
    defaultValue: "",
    validate: (value) => validateLastname(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const firstname = useFormField({
    defaultValue: "",
    validate: (value) => validateFirstname(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const company = useFormField({
    defaultValue: "",
    validate: (value) => validateCompany(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const street1 = useFormField({
    defaultValue: "",
    validate: (value) => validateStreet1(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const street2 = useFormField({
    defaultValue: "",
  });

  const telephone = useFormField({
    defaultValue: "",
    validate: (value) => validateTelephone(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const postalCode = useFormField({
    defaultValue: "",
    validate: (value) => validatePostalCode(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const town = useFormField({
    defaultValue: "",
  });

  const city = useFormField({
    defaultValue: "",
    validate: (value) => validateCity(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const state = useFormField({
    defaultValue: "",
  });

  const country = useFormApiAutoCompleteDropdown({
    defaultValue: null,
    fetchData: fetchCountry,
    validate: (value) => validateCountry(value, isNewAddress),
    dependsOn: [isNewAddress],
  });

  const deliveryNotes = useFormField({
    defaultValue: "",
  });

  const formValidator = useFormValidator([
    parts,
    contact,
    lastname,
    firstname,
    company,
    street1,
    telephone,
    postalCode,
    city,
    country,
    address,
    type,
  ]);

  const handleSubmit = () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      let countryValue = "";
      if (country.value?.additionalInfo?.type === "Country") {
        countryValue = country.value.additionalInfo.data.isoCode2;
      }
      const params: ITocSprFormData = {
        deliveryAddress: address.value,
        deliveryNotes: deliveryNotes.value,
        parts: parts.value.map((part) => ({
          partNumber: part.partNumber?.id ?? "",
          description: part.description,
          quantity: +part.quantity,
          unitOfMeasure: part.uom,
          comment: part.comment,
        })),
      };
      if (type.value?.id === SPR_ADDRESS.new) {
        params.deliveryAddress = {
          contact: contact.value?.id ?? null,
          firstname: firstname.value,
          lastname: lastname.value,
          company: company.value,
          airport: airport.value?.id ?? null,
          phone: telephone.value,
          address: {
            street1: street1.value,
            street2: street2.value,
            postalCode: postalCode.value,
            town: town.value,
            city: city.value,
            state: state.value,
            country: countryValue,
          },
        };
      }
      onSubmit(params);
    }
  };

  const handleAddParts = () => {
    parts.onAdd(INITIAL_TOC_SPR_PART_DATA);
  };

  const fetchContactAndAddressList = async () => {
    const contactPromise = getAllSprContacts({ customers });
    const addressPromise = getAllSprAddress({ customers });
    const [contactResponse, addressResponse] = await Promise.all([
      contactPromise,
      addressPromise,
    ]);
    if (contactResponse.data) {
      setContactList(
        contactResponse.data["hydra:member"].map((item) =>
          createContactDropdownItem(item)
        )
      );
    }
    if (addressResponse.data) {
      setAddressList(
        addressResponse.data["hydra:member"].map((item) => formatAddress(item))
      );
    }
    dispatch(showMainLoader(false));
  };

  useEffect(() => {
    fetchContactAndAddressList();
  }, []);

  return (
    <div className="toc_spr_form__wrapper">
      <div className="toc_spr_form__header_wrapper">
        <Typography variant="h5" gutterBottom>
          {t("toc_spr_form.parts.title")}
        </Typography>
        <Avatar className="cui_action" color="primary">
          <IconButton onClick={handleAddParts} data-cy="toc-detail-parts-add">
            <AddIcon />
          </IconButton>
        </Avatar>
      </div>
      {parts.data.map((part, idx) => (
        <TocSprPartForm
          key={part.id}
          index={idx + 1}
          onChange={(data) =>
            parts.onChange({
              id: part.id,
              data: data.data,
              isFormErrorFree: data.isFormErrorFree,
              isSubmitDisabled: data.isSubmitDisabled,
            })
          }
          onDelete={() => parts.onDelete(part.id)}
          isFormTouched={parts.isFormTouched}
        />
      ))}
      <div className="toc_spr_form__address_wrapper">
        <div>
          <Typography
            className="cui_light_text"
            variant="h5"
            gutterBottom
            data-cy="toc-spr-form-address-heading"
          >
            {t("toc_spr_form.address.title")}
          </Typography>
        </div>
        <div className="toc_spr_form__address_fields">
          <FormSingleSelectDropdown
            {...type.fieldProps}
            requiredLabel
            label="toc_spr_form.address.type.title"
            placeholder="toc_spr_form.address.type.placeholder"
            dataCy="toc-spr-part-form-type"
          />

          {type.value?.id === SPR_ADDRESS.existing && (
            <FormRadioButtons
              {...address.fieldProps}
              requiredLabel
              label="toc_spr_form.address.delivery_address.title"
              dataCy="toc-spr-part-form-delivery-address"
            />
          )}
          {type.value?.id === SPR_ADDRESS.new && (
            <>
              <FormSingleSelectDropdown
                {...contact.fieldProps}
                label="toc_spr_form.address.econtact.title"
                placeholder="toc_spr_form.address.econtact.placeholder"
                dataCy="toc-spr-part-form-econtact"
              />
              <FormApiAutoCompleteDropdown
                {...airport.fieldProps}
                label="toc_spr_form.address.airport.title"
                id="airport"
                placeholder="toc_spr_form.address.airport.placeholder"
                dataCy="toc-spr-part-form-airport"
              />
              <FormField
                {...lastname.fieldProps}
                requiredLabel
                label="toc_spr_form.address.lastname.title"
                id="lastname"
                placeholder="toc_spr_form.address.lastname.placeholder"
                dataCy="toc-spr-part-form-lastname"
              />
              <FormField
                {...firstname.fieldProps}
                requiredLabel
                label="toc_spr_form.address.firstname.title"
                id="uom"
                placeholder="toc_spr_form.address.firstname.placeholder"
                dataCy="toc-spr-part-form-firstname"
              />
              <FormField
                {...company.fieldProps}
                requiredLabel
                label="toc_spr_form.address.company.title"
                id="company"
                placeholder="toc_spr_form.address.company.placeholder"
                dataCy="toc-spr-part-form-company"
              />
              <FormField
                {...street1.fieldProps}
                requiredLabel
                label="toc_spr_form.address.street_1.title"
                id="street-1"
                placeholder="toc_spr_form.address.street_1.placeholder"
                dataCy="toc-spr-part-form-street-1"
              />
              <FormField
                {...street2.fieldProps}
                label="toc_spr_form.address.street_2.title"
                id="street-2"
                placeholder="toc_spr_form.address.street_2.placeholder"
                dataCy="toc-spr-part-form-street-2"
              />
              <FormField
                {...telephone.fieldProps}
                requiredLabel
                label="toc_spr_form.address.telephone.title"
                id="telephone"
                placeholder="toc_spr_form.address.telephone.placeholder"
                dataCy="toc-spr-part-form-telephone"
              />
              <FormField
                {...postalCode.fieldProps}
                requiredLabel
                label="toc_spr_form.address.postal_code.title"
                id="postal-code"
                placeholder="toc_spr_form.address.postal_code.placeholder"
                dataCy="toc-spr-part-form-postal-code"
              />
              <FormField
                {...town.fieldProps}
                label="toc_spr_form.address.town.title"
                id="town"
                placeholder="toc_spr_form.address.town.placeholder"
                dataCy="toc-spr-part-form-town"
              />
              <FormField
                {...city.fieldProps}
                requiredLabel
                label="toc_spr_form.address.city.title"
                id="city"
                placeholder="toc_spr_form.address.city.placeholder"
                dataCy="toc-spr-part-form-city"
              />
              <FormField
                {...state.fieldProps}
                label="toc_spr_form.address.state.title"
                id="state"
                placeholder="toc_spr_form.address.state.placeholder"
                dataCy="toc-spr-part-form-state"
              />
              <FormApiAutoCompleteDropdown
                {...country.fieldProps}
                requiredLabel
                label="toc_spr_form.address.country.title"
                id="country"
                placeholder="toc_spr_form.address.country.placeholder"
                dataCy="toc-spr-part-form-country"
              />
            </>
          )}
          <FormField
            {...deliveryNotes.fieldProps}
            label="toc_spr_form.address.delivery_notes.title"
            id="delivery-notes"
            placeholder="toc_spr_form.address.delivery_notes.placeholder"
            dataCy="toc-spr-part-form-delivery-notes"
            rows={4}
          />
        </div>
      </div>
      <Button
        disabled={formValidator.isSubmitDisabled}
        className="cui_button"
        type="submit"
        variant="contained"
        onClick={handleSubmit}
        data-cy="toc-spr-part-form-submit"
      >
        {t("toc_parts_form.submit")}
      </Button>
    </div>
  );
}
