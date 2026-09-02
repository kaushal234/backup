import React, { useEffect, useRef, useState } from "react";
import {
  change,
  FieldArray,
  InjectedFormProps,
  reduxForm,
  WrappedFieldArrayProps,
} from "redux-form";
import { Divider } from "@mui/material";
import Translator from "bazinga-translator";
import { validate } from "../../model/form/contract_form/validation";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import Accordion from "../Accordion/Accordion";
import "./ContractForm.css";
import {
  IContractFormData,
  IContractInternalPartyFormData,
  IContractOtherPartySignatoriesFormData,
} from "../../types/IContractFormData";
import { fetchAllCurrency } from "../../utils/dropdown/currency";
import { fetchAllContractSubCategory } from "../../utils/dropdown/contractSubCategory";
import { fetchDivision } from "../../utils/dropdown/division";
import { fetchAllRegion } from "../../utils/dropdown/region";
import { fetchPremise } from "../../utils/dropdown/premise";
import {
  CONTRACT_CUSTOMERS_SUB_CATEGORIES,
  CONTRACT_RENEWAL_UNIT_OPTIONS,
} from "../../constants/constants";
import { fetchAllContract } from "../../utils/dropdown/contract";
import { fetchPeople } from "../../utils/dropdown/people";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { IDropdownItem } from "../../types/IDropdownItem";
import { fetchAllBusinessUnit } from "../../utils/dropdown/businessUnit";
import { IFetchAllRegionParams } from "../../types/IFetchAllRegionParams";
import { IFetchAllBusinessUnitParams } from "../../types/IFetchAllBusinessUnitParams";
import { fetchAllContractCategory } from "../../utils/dropdown/contractCategory";
import FullScreenLoader from "../FullScreenLoader/FullScreenLoader";

const formName = "sample_form";

function InternalPartyArray({
  fields,
  meta,
}: WrappedFieldArrayProps<IContractInternalPartyFormData>) {
  return (
    <div className="contract_form__right_align">
      <GenericFormComponent
        type="Label"
        label={Translator.trans("contract.form.parties.internal_party.label")}
        labelTooltip={Translator.trans(
          "contract.form.parties.internal_party.hint"
        )}
        required
      />
      <div className="contract_form__array_field">
        {fields.map((name, index) => (
          <div className="contract_form__right_align" key={name}>
            <GenericFormComponent type="Field" name={name} />
            <button
              className="btn btn-danger mt-1"
              type="button"
              onClick={() => fields.remove(index)}
            >
              <i className="fa fa-fw fa-trash" />{" "}
              {Translator.trans("contract.form.action.delete")}
            </button>
          </div>
        ))}
      </div>
      <button
        className="btn btn-info mt-3"
        type="button"
        id="addInternalPartyButton"
        onClick={() => fields.push("")}
      >
        {Translator.trans("contract.form.action.add")}
      </button>
      <GenericFormComponent
        type="Error"
        touched={meta.submitFailed}
        error={meta.error}
        align="end"
      />
    </div>
  );
}

function OtherPartySignatoriesArray({
  fields,
}: WrappedFieldArrayProps<IContractOtherPartySignatoriesFormData>) {
  return (
    <div className="contract_form__right_align">
      <GenericFormComponent
        type="Label"
        label={Translator.trans(
          "contract.form.parties.other_party_signatories.label"
        )}
      />
      <div className="contract_form__array_field">
        {fields.map((name, index) => (
          <div className="contract_form__right_align" key={name}>
            <GenericFormComponent type="Field" name={name} />
            <button
              className="btn btn-danger mt-1"
              type="button"
              onClick={() => fields.remove(index)}
            >
              <i className="fa fa-fw fa-trash" />{" "}
              {Translator.trans("contract.form.action.delete")}
            </button>
          </div>
        ))}
      </div>
      <button
        className="btn btn-info mt-3"
        type="button"
        id="addOtherPartySignatoriesButton"
        onClick={() => fields.push("")}
      >
        {Translator.trans("contract.form.action.add")}
      </button>
    </div>
  );
}

export interface IContractFormProps {
  isEdit?: boolean;
  onSubmit: (values: IContractFormData) => void;
}

type IWrappedProps = IContractFormProps &
  InjectedFormProps<IContractFormData, IContractFormProps>;

function ContractForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid, onSubmit, isEdit } =
    props;

  const dispatch = useAppDispatch();

  const formValues: IContractFormData | undefined = useAppSelector(
    (state) => state.form[formName]?.values
  );

  const isFirstCategoryRun = useRef(true);
  const [isCustomerRequired, setIsCustomerRequired] = useState(false);
  const [regionList, setRegionList] = useState<Array<IDropdownItem>>([]);
  const [subCategoryList, setSubCategoryList] = useState<Array<IDropdownItem>>(
    []
  );
  const [businessUnitList, setBusinessUnitList] = useState<
    Array<IDropdownItem>
  >([]);

  useEffect(() => {
    if (formValues?.indefinitePeriodType) {
      dispatch(change(formName, "expirationDate", null));
      dispatch(change(formName, "renewalPeriod", ""));
      dispatch(change(formName, "renewalUnit", null));
    }
  }, [formValues?.indefinitePeriodType]);

  const handleSubCategoryChange = (item: IDropdownItem) => {
    if (CONTRACT_CUSTOMERS_SUB_CATEGORIES.includes(item.value)) {
      setIsCustomerRequired(true);
    } else {
      setIsCustomerRequired(false);
    }
  };

  const fetchSubCategoryList = async () => {
    if (!isFirstCategoryRun.current) {
      dispatch(change(formName, "subCategory", undefined));
    }
    isFirstCategoryRun.current = false;
    let newSubCategoryList: Array<IDropdownItem> = [];
    if (formValues?.category) {
      newSubCategoryList = await fetchAllContractSubCategory({
        category: formValues.category.value,
      });
    }
    setSubCategoryList(newSubCategoryList);
  };

  const fetchRegionList = async () => {
    let filters: IFetchAllRegionParams = { pagination: false };
    if (formValues?.divisions?.length) {
      filters = {
        division: formValues.divisions.map((item) => item.value),
        pagination: false,
      };
    }
    dispatch(change(formName, "regions", undefined));
    setRegionList(await fetchAllRegion(filters));
  };

  const fetchBusinessUnitList = async () => {
    let filters: IFetchAllBusinessUnitParams = { pagination: false };
    if (formValues?.regions?.length) {
      filters = {
        region: formValues.regions.map((item) => item.value),
        pagination: false,
      };
    } else if (formValues?.divisions?.length) {
      filters = {
        division: formValues.divisions.map((item) => item.value),
        pagination: false,
      };
    }
    dispatch(change(formName, "businessUnits", undefined));
    setBusinessUnitList(await fetchAllBusinessUnit(filters));
  };

  useEffect(() => {
    fetchSubCategoryList();
  }, [formValues?.category]);

  useEffect(() => {
    fetchRegionList();
  }, [formValues?.divisions]);

  useEffect(() => {
    fetchBusinessUnitList();
  }, [formValues?.regions, formValues?.divisions]);

  return (
    <div className="contract_form__wrapper">
      <FullScreenLoader />
      <Accordion
        title={
          isEdit
            ? Translator.trans("contract.edit.accordion.title")
            : Translator.trans("contract.add.accordion.title")
        }
      >
        <div>
          <form noValidate onSubmit={handleSubmit(onSubmit)}>
            <div className="contract_form__section_wrapper">
              <div className="contract_form__section_heading">
                {Translator.trans("contract.form.general.heading")}
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.general.shortDescription.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.general.shortDescription.placeholder"
                  )}
                  name="shortDescription"
                  required
                />

                <div>
                  <GenericFormComponent
                    type="SingleSelectDynamicDropdown"
                    label={Translator.trans(
                      "contract.form.general.category.label"
                    )}
                    name="category"
                    fetchList={fetchAllContractCategory}
                    required
                  />
                  <GenericFormComponent
                    type="SingleSelectStaticDropdown"
                    label={Translator.trans(
                      "contract.form.general.sub_category.label"
                    )}
                    name="subCategory"
                    onChange={handleSubCategoryChange}
                    list={subCategoryList}
                    required
                  />
                </div>
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.general.description.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.general.description.hint"
                  )}
                  name="description"
                  required
                  isTextArea
                />
                <div>
                  <GenericFormComponent
                    type="SingleSelectDynamicDropdown"
                    label={Translator.trans(
                      "contract.form.general.parent_contract.label"
                    )}
                    labelTooltip={Translator.trans(
                      "contract.form.general.parent_contract.hint"
                    )}
                    name="parentContract"
                    fetchList={fetchAllContract}
                  />
                  {isEdit && (
                    <GenericFormComponent
                      type="SingleSelectAutoCompleteDropdown"
                      label={Translator.trans(
                        "contract.form.general.owner.label"
                      )}
                      name="owner"
                      fetchList={fetchPeople}
                      required
                    />
                  )}
                  <GenericFormComponent
                    type="MutliSelectAutoCompleteDropdown"
                    label={Translator.trans(
                      "contract.form.general.customer.label"
                    )}
                    name="customers"
                    fetchList={fetchCustomer}
                    required={isCustomerRequired}
                  />
                </div>
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.general.jurisdiction.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.general.jurisdiction.placeholder"
                  )}
                  name="jurisdiction"
                />
                <div className="contract_form__row">
                  <GenericFormComponent
                    type="DatePicker"
                    label={Translator.trans(
                      "contract.form.general.start_date.label"
                    )}
                    labelTooltip={Translator.trans(
                      "contract.form.general.start_date.hint"
                    )}
                    name="startDate"
                    required
                  />
                </div>
              </div>
              <div className="contract_form__row contract_form__checkbox_row">
                <GenericFormComponent
                  type="Checkbox"
                  label={Translator.trans(
                    "contract.form.general.confidential.label"
                  )}
                  name="confidential"
                  checkboxFirst
                />
              </div>
            </div>
            <Divider />
            <div className="contract_form__section_wrapper">
              <div className="contract_form__section_heading">
                <GenericFormComponent
                  type="Label"
                  label={Translator.trans("contract.form.organization.heading")}
                  labelTooltip={Translator.trans(
                    "contract.form.organization.hint"
                  )}
                />
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="MutliSelectAutoCompleteDropdown"
                  label={Translator.trans(
                    "contract.form.organization.division.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.organization.division.placeholder"
                  )}
                  name="divisions"
                  fetchList={fetchDivision}
                  triggerAtChar={0}
                />
                <GenericFormComponent
                  type="MutliSelectStaticDropdown"
                  label={Translator.trans(
                    "contract.form.organization.region.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.organization.region.placeholder"
                  )}
                  name="regions"
                  list={regionList}
                />
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="MutliSelectStaticDropdown"
                  label={Translator.trans(
                    "contract.form.organization.business_unit.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.organization.business_unit.placeholder"
                  )}
                  name="businessUnits"
                  list={businessUnitList}
                  required
                />
                <GenericFormComponent
                  type="MutliSelectAutoCompleteDropdown"
                  label={Translator.trans(
                    "contract.form.organization.premise.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.organization.premise.placeholder"
                  )}
                  name="premises"
                  fetchList={fetchPremise}
                  triggerAtChar={0}
                />
              </div>
            </div>
            <Divider />
            <div className="contract_form__section_wrapper">
              <div className="contract_form__section_heading">
                {Translator.trans("contract.form.values.heading")}
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans("contract.form.values.value.label")}
                  labelTooltip={Translator.trans(
                    "contract.form.values.value.hint"
                  )}
                  name="value"
                  allowNumbersOnly
                />
                <GenericFormComponent
                  type="SingleSelectDynamicDropdown"
                  label={Translator.trans(
                    "contract.form.values.currency.label"
                  )}
                  name="currency"
                  fetchList={fetchAllCurrency}
                />
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.values.observations.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.values.observations.hint"
                  )}
                  name="observationValue"
                  isTextArea
                />
                <div />
              </div>
            </div>
            <Divider />
            <div className="contract_form__section_wrapper">
              <div className="contract_form__section_heading">
                {Translator.trans("contract.form.term.heading")}
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="DatePicker"
                  label={Translator.trans(
                    "contract.form.term.expiration_date.label"
                  )}
                  name="expirationDate"
                  disabled={formValues?.indefinitePeriodType}
                />
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.term.renewal_period.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.term.renewal_period.hint"
                  )}
                  name="renewalPeriod"
                  allowNumbersOnly
                  disabled={formValues?.indefinitePeriodType}
                />
                <GenericFormComponent
                  type="SingleSelectStaticDropdown"
                  label={Translator.trans(
                    "contract.form.term.renewal_unit.label"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.term.renewal_unit.placeholder"
                  )}
                  name="renewalUnit"
                  list={CONTRACT_RENEWAL_UNIT_OPTIONS}
                  disabled={formValues?.indefinitePeriodType}
                />
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.term.observations.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.term.observations.hint"
                  )}
                  name="observationTerm"
                  isTextArea
                />
                <div />
              </div>
              <div className="contract_form__row contract_form__checkbox_row">
                <GenericFormComponent
                  type="Checkbox"
                  label={Translator.trans(
                    "contract.form.term.indefinite_period_type.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.term.indefinite_period_type.hint"
                  )}
                  name="indefinitePeriodType"
                  checkboxFirst
                />
              </div>
              <div className="contract_form__row contract_form__checkbox_row">
                <GenericFormComponent
                  type="Checkbox"
                  label={Translator.trans(
                    "contract.form.term.automatic_renewal.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.term.automatic_renewal.hint"
                  )}
                  name="automaticRenewal"
                  checkboxFirst
                />
              </div>
            </div>
            <Divider />
            <div className="contract_form__section_wrapper">
              <div className="contract_form__section_heading">
                {Translator.trans("contract.form.parties.heading")}
              </div>
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.parties.external_party.label"
                  )}
                  labelTooltip={Translator.trans(
                    "contract.form.parties.external_party.hint"
                  )}
                  placeholder={Translator.trans(
                    "contract.form.parties.external_party.placeholder"
                  )}
                  name="externalParty"
                  required
                />
                <FieldArray
                  name="internalParty"
                  component={InternalPartyArray}
                />
                <FieldArray
                  name="otherPartySignatories"
                  component={OtherPartySignatoriesArray}
                />
              </div>
            </div>
            <Divider />
            <div className="contract_form__section_wrapper">
              <div className="contract_form__row">
                <GenericFormComponent
                  type="Field"
                  label={Translator.trans(
                    "contract.form.general.comment.label"
                  )}
                  name="comment"
                  isTextArea
                />
              </div>
            </div>
            <div className="contract_form__right_align">
              <button
                className="btn btn-info mt-3"
                type="submit"
                disabled={submitting || (submitFailed && invalid)}
              >
                {Translator.trans("contract.form.action.submit")}
              </button>
            </div>
          </form>
        </div>
      </Accordion>
    </div>
  );
}

export default reduxForm<IContractFormData, IContractFormProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ContractForm);
