import React, { useEffect, useRef, useState } from "react";
import Swal from "sweetalert2";
import {
  getFormValues,
  reduxForm,
  change,
  InjectedFormProps,
  autofill,
} from "redux-form";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { Modal, Alert } from "react-bootstrap";
import NestedCustomerServiceRecordForm from "../../components/Forms/TechnicianOnCall/NestedCustomerServiceRecordForm";
import { TechnicianOnCallFiles } from "../../components/TechnicianOnCall/TechnicianOnCallFiles";
import Loader from "../../components/Loader";
import validate from "../../model/form/technician_on_call/validation";
import {
  technicianOnCallFormFactory,
  technicianOnCallWriteFactory,
} from "../../model/form/technician_on_call/factory";
import { RootState } from "../../store";
import ExtranetUserForm from "../ExtranetUser/ExtranetUserForm";
import { useAppDispatch } from "../../hooks/hooks";
import "./technicianOnCallForm.css";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";
import { IDropdownItem } from "../../types/IDropdownItem";
import { fetchAllExtranetUsersRelatedToCustomer } from "../../utils/dropdown/extranetUsers";
import { fetchFilteredPeople, fetchPeople } from "../../utils/dropdown/people";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";
import { getEquipmentRecordById } from "../../api/getEquipmentRecordById";
import { useEffectWithParam } from "../../hooks/useEffectWithParam";
import {
  fetchAllExtranetUsersRelatedToEr,
  IErExtranetUserData,
} from "../../utils/dropdown/erExtranetUsers";
import {
  fetchEquipmentRecordSerialNo,
  formatEquipmentRecordOptionLabel,
} from "../../utils/dropdown/equipmentRecordSerialNumber";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { fetchAirport } from "../../utils/dropdown/airport";
import { fetchServiceOrganisationPage } from "../../utils/dropdown/serviceOrganisation";
import { IFACTOR_OPTIONS } from "../../constants/constants";
import { fetchUnitOperationalStatusOptions } from "../../utils/dropdown/unitOperationalStatus";
import {
  fetchDefaultTechnicianOnCallType,
  fetchTechnicianOnCallTypeOptions,
} from "../../utils/dropdown/technicianOnCallType";
import { fetchServiceActivityOptions } from "../../utils/dropdown/serviceActivity";
import { fetchTechnicianOnCallTagOptions } from "../../utils/dropdown/technicianOnCallTag";
import { ITechnicianOnCallFormData } from "../../types/ITechnicianOnCallFormData";
import { postTechnicianOnCall } from "../../api/postTechnicianOnCall";
import { putTechnicianOnCall } from "../../api/putTechnicianOnCall";
import {
  tocHideErrorAlert,
  tocHideSuccessAlert,
} from "../../reducers/technicianOnCall/technicianOnCallReducer";

const formName = "technician_on_call_form";

interface IProps {
  formType: string;
  showSuccess: boolean;
  showError: boolean;
  errorMessage: string;
  showFiles: boolean;
  formValues: ITechnicianOnCallFormData;
  canCreateCsr?: boolean;
  canCreateExtranetUser?: boolean;
  id: number;
}

type IWrappedProps = IProps &
  InjectedFormProps<ITechnicianOnCallFormData, IProps>;

function TechnicianOnCallForm(props: IWrappedProps) {
  const {
    formType,
    handleSubmit,
    showSuccess,
    showError,
    errorMessage,
    showFiles,
    formValues,
    valid,
    submitting,
    canCreateCsr,
    canCreateExtranetUser,
    initialValues,
    initialized,
  } = props;

  const dispatch = useAppDispatch();
  const isFormInitializedRef = useRef(formType === "add");
  const [redirectFlag, setRedirectFlag] = useState(false);
  const [createNestedCsr, setCreateNestedCsr] = useState(false);
  const [showNewContactModal, setShowNewContactModal] = useState(false);

  const [contactsList, setContactsList] = useState<Array<IDropdownItem>>([]);
  const [otherContactsList, setOtherContactsList] = useState<
    Array<IDropdownItem>
  >([]);

  const fetchContacts = async (searchText?: string) => {
    const response = await fetchAllExtranetUsersRelatedToCustomer(
      formValues?.customer?.value ?? "",
      searchText
    );
    setContactsList(response);
  };

  const handleMainContactInputChange = (input: string) => {
    if (!input || input.length < 3) {
      fetchContacts();
      return;
    }
    fetchContacts(input);
  };

  const fetchOtherContacts = async (searchText?: string) => {
    const list = await fetchAllExtranetUsersRelatedToEr(
      formValues?.equipmentRecord?.value ?? "",
      searchText
    );
    setOtherContactsList(list);
    return list;
  };

  const handleContactsInputChange = (input: string) => {
    if (!input || input.length < 3) {
      fetchOtherContacts();
      return;
    }
    fetchOtherContacts(input);
  };

  const initialConfidential = !!initialValues?.confidential;
  const currentConfidential = !!formValues?.confidential;

  const shouldShowConfidentialReason =
    initialized && currentConfidential !== initialConfidential;

  const selectedEquipmentRecordServiceSsoIri =
    formValues?.equipmentRecord?.data?.data?.salesOrganisationService?.["@id"];
  const selectedServiceSsoIri = formValues?.salesOrganisationService?.value;
  const serviceSsoMismatch =
    formValues?.equipmentRecord &&
    selectedEquipmentRecordServiceSsoIri !== selectedServiceSsoIri;

  const selectedEquipmentRecordAirportIri =
    formValues?.equipmentRecord?.data?.data?.airport?.["@id"];
  const selectedAirportIri = formValues?.airport?.value;
  const airportMismatch =
    formValues?.equipmentRecord &&
    selectedEquipmentRecordAirportIri !== selectedAirportIri;

  const selectedEquipmentRecordCustomerSerialNumber =
    formValues?.equipmentRecord?.data?.data?.customerSerialNumber;
  const selectedCustomerSerialNumber = formValues?.serialNumber;
  const customerSerialNumberMismatch =
    formValues?.equipmentRecord &&
    selectedEquipmentRecordCustomerSerialNumber !==
      selectedCustomerSerialNumber;

  useEffect(() => {
    if (showNewContactModal === false && formValues?.customer?.value) {
      fetchContacts();
    }
  }, [showNewContactModal, formValues?.customer?.value]);

  const handleSuccess = () => {
    Swal.fire({
      icon: "success",
      title: "Saved",
      confirmButtonText: "OK",
    }).then(() => {
      dispatch(tocHideSuccessAlert());
    });

    if (formType === "edit") {
      setRedirectFlag(true);
    }
  };

  useEffect(() => {
    if (showSuccess) {
      handleSuccess();
    }
  }, [showSuccess]);

  const handleError = (error?: string) => {
    Swal.fire({
      icon: "warning",
      title: Translator.trans(
        `toc.messages.errors.${formType === "add" ? "creation" : "edition"}`
      ),
      text: error || errorMessage || "",
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "OK",
    }).then(() => dispatch(tocHideErrorAlert()));
  };

  useEffect(() => {
    if (showError) {
      handleError();
    }
  }, [showError]);

  const onSubmit = async (values: any) => {
    const submitValues = { ...values };

    if (createNestedCsr && !submitValues.nestedCustomerServiceRecord) {
      submitValues.nestedCustomerServiceRecord = {
        leader: null,
        plannedAt: null,
      };
    }

    if (!createNestedCsr) {
      delete submitValues.nestedCustomerServiceRecord;
    }

    const submittedConfidential = !!values.confidential;
    const confidentialChanged = submittedConfidential !== initialConfidential;
    const tocPayload = technicianOnCallWriteFactory(
      submitValues,
      confidentialChanged
    );

    const response =
      formType === "add"
        ? await postTechnicianOnCall(tocPayload)
        : await putTechnicianOnCall({
            id: `${formValues?.id ?? ""}`,
            data: tocPayload,
          });

    if (
      response.status === 200 ||
      response.status === 201 ||
      response.status === 206
    ) {
      dispatch(autofill(formName, "id", response.data?.id));
      dispatch(
        autofill(
          formName,
          "nestedCustomerServiceRecord.id",
          response.data?.["@sub_resources"]?.customerServiceRecord?.id
        )
      );
      handleSuccess();
    } else {
      handleError(response.errorMessage);
    }
  };

  const onEquipmentRecordChange = (equipmentRecord: any) => {
    if (equipmentRecord.airport) {
      dispatch(
        change(formName, "airport", {
          value: equipmentRecord.airport["@id"],
          label: `${equipmentRecord.airport.code} - ${equipmentRecord.airport.cityName}`,
        })
      );
    }
    if (equipmentRecord.salesOrganisationService) {
      dispatch(
        change(formName, "salesOrganisationService", {
          value: equipmentRecord.salesOrganisationService["@id"],
          label: equipmentRecord.salesOrganisationService.name,
        })
      );
    }

    if (equipmentRecord.endUser) {
      dispatch(
        change(formName, "customer", {
          value: equipmentRecord.endUser["@id"],
          label: equipmentRecord.endUser.name,
        })
      );
    }

    dispatch(
      change(
        formName,
        "serialNumber",
        equipmentRecord.customerSerialNumber ?? null
      )
    );
  };

  const initializeEquipmentRecord = async () => {
    const params = new URLSearchParams(window.location.search);
    const equipmentRecordId = params.get("equipmentRecordId");

    if (equipmentRecordId) {
      dispatch(showGlobalLoader(true));
      const equipmentRecord = await getEquipmentRecordById({
        id: equipmentRecordId,
        normalization_groups: ["equipment_record:service", "iata_code_detail"],
      });
      dispatch(showGlobalLoader(false));

      if (equipmentRecord.data) {
        dispatch(
          change(formName, "equipmentRecord", {
            value: equipmentRecord.data["@id"],
            label: `${equipmentRecord.data.type} / ${equipmentRecord.data.model} - ${equipmentRecord.data.serialNumber}`,
            data: equipmentRecord.data,
          })
        );
        onEquipmentRecordChange(equipmentRecord.data);
      }
    }
  };

  useEffect(() => {
    initializeEquipmentRecord();
  }, []);

  useEffect(() => {
    if (formType !== "add") return;
    fetchDefaultTechnicianOnCallType().then((defaultType) => {
      if (defaultType) {
        dispatch(change(formName, "technicianOnCallType", defaultType));
      }
    });
  }, []);

  const handleCreateNestedCsr = () => {
    setCreateNestedCsr(!createNestedCsr);
    dispatch(
      change(formName, "nestedCustomerServiceRecord.leader", {
        value: window.user.iriId,
        label: window.user.username,
      })
    );
    dispatch(
      change(formName, "nestedCustomerServiceRecord.plannedAt", new Date())
    );
  };

  const customerInitializedRef = useRef(false);

  useEffect(() => {
    if (!formValues?.customer?.value) {
      setContactsList([]);
      return;
    }
    if (!customerInitializedRef.current) {
      customerInitializedRef.current = true;
      fetchContacts();
      return;
    }
    dispatch(change(formName, "mainContact", null));
    fetchContacts();
  }, [formValues?.customer?.value]);

  const getFlNotTocContacts = (list: IDropdownItem[]): IDropdownItem[] =>
    list.filter((item) =>
      ((item.data as IErExtranetUserData)?.extranetUserAcls ?? []).some(
        (acl) => acl.extranetUserGroup?.name === "fl_NOT_TOC"
      )
    );

  useEffectWithParam(
    (isFirstRender) => {
      if (!formValues?.equipmentRecord?.value) {
        setOtherContactsList([]);
        if (!isFirstRender && isFormInitializedRef.current) {
          dispatch(change(formName, "contacts", null));
        }
        return;
      }

      fetchOtherContacts().then((newList) => {
        const flNotTocContacts = getFlNotTocContacts(newList);

        if (
          !isFormInitializedRef.current &&
          formType === "add" &&
          flNotTocContacts.length > 0
        ) {
          dispatch(change(formName, "contacts", flNotTocContacts));
          return;
        }

        if (!isFormInitializedRef.current) {
          return;
        }

        const currentContacts: IDropdownItem[] = formValues?.contacts ?? [];
        const filteredContacts = currentContacts.filter((contact) =>
          newList.some((item) => item.value === contact.value)
        );
        const mergedContacts = [
          ...filteredContacts,
          ...flNotTocContacts.filter(
            (flContact) =>
              !filteredContacts.some((c) => c.value === flContact.value)
          ),
        ];
        dispatch(
          change(
            formName,
            "contacts",
            mergedContacts.length > 0 ? mergedContacts : null
          )
        );
      });
    },
    [formValues?.equipmentRecord?.value]
  );

  useEffectWithParam(
    (isFirstRender) => {
      if (isFirstRender || !isFormInitializedRef.current) return;
      if (formValues?.serviceActivity?.label === "Info request") {
        dispatch(
          change(formName, "unitOperationalStatus", {
            value: "/unit_operational_statuses/MCF",
            label: "Mission Capable Fully - MCF",
          })
        );
        dispatch(change(formName, "indiceFactor", IFACTOR_OPTIONS[0]));
      }

      if (formValues?.serviceActivity?.label === "Commissioning") {
        dispatch(change(formName, "confidential", true));
        dispatch(
          change(
            formName,
            "confidentialReason",
            Translator.trans(
              "toc.fields.confidential_reason.autofill_commissioning"
            )
          )
        );
      }
    },
    [formValues?.serviceActivity?.value]
  );

  useEffectWithParam(
    (isFirstRender) => {
      if (isFirstRender) return;
      if (
        formValues?.serviceActivity?.label === "Commissioning" &&
        formValues?.confidential === false
      ) {
        dispatch(change(formName, "serviceActivity", null));
        dispatch(change(formName, "confidentialReason", null));
      }
    },
    [formValues?.confidential]
  );

  useEffectWithParam(
    (isFirstRender) => {
      if (isFirstRender || !isFormInitializedRef.current) return;
      if (
        formValues?.serviceActivity?.label === "Info request" &&
        formValues?.indiceFactor?.value !== "IF 1"
      ) {
        dispatch(
          change(formName, "serviceActivity", {
            value: "/service/service_activities/1",
            label: "Troubleshooting",
          })
        );
      }
    },
    [formValues?.indiceFactor?.value]
  );

  useEffect(() => {
    if (formValues?.id && !isFormInitializedRef.current) {
      isFormInitializedRef.current = true;
    }
  }, [formValues?.id]);

  if (redirectFlag) {
    window.location.href = `/en/private/service/technician-on-calls/${formValues.id}/show`;
    return <Loader />;
  }

  return (
    <>
      <FullScreenLoader />
      <div style={{ position: "relative" }}>
        {!showFiles && (
          <>
            <div className="row">
              <form
                onSubmit={handleSubmit(onSubmit)}
                style={{
                  opacity: submitting || (!showSuccess && showFiles) ? 0.3 : 1,
                  pointerEvents:
                    submitting || (!showSuccess && showFiles) ? "none" : "auto",
                }}
              >
                <div className="ibox float-e-margin technician_on_call_form__wrapper">
                  <h1>
                    {Translator.trans(
                      `toc.title.${formType === "edit" ? "edit" : "add"}`
                    )}
                  </h1>
                  <div className="ibox-content row">
                    <div className="col-7">
                      <div className="row">
                        <div className="col-6">
                          <GenericFormComponent
                            type="SingleSelectAutoCompleteDropdown"
                            label={Translator.trans(
                              "service.equipment_record.fields.serial_number"
                            )}
                            name="equipmentRecord"
                            fetchList={fetchEquipmentRecordSerialNo}
                            formatOptionLabel={formatEquipmentRecordOptionLabel}
                            onChange={(item) => {
                              onEquipmentRecordChange(item.data?.data);
                            }}
                          />
                        </div>
                        <div className="col-6">
                          <GenericFormComponent
                            type="Field"
                            name="serialNumber"
                            label={Translator.trans("toc.fields.serialNumber")}
                            labelTooltip={
                              customerSerialNumberMismatch && formType === "add"
                                ? Translator.trans(
                                    "toc.messages.errors.customer_asset_mismatch"
                                  )
                                : Translator.trans(
                                    "toc.messages.help.serialNumber"
                                  )
                            }
                          />
                        </div>
                      </div>
                      <div className="row">
                        <div className="col-6">
                          <GenericFormComponent
                            type="SingleSelectAutoCompleteDropdown"
                            label={Translator.trans("tasks.assignee")}
                            labelTooltip={Translator.trans(
                              "toc.messages.help.assignee"
                            )}
                            name="assignee"
                            fetchList={fetchPeople}
                            required
                          />
                        </div>
                        <div className="col-6">
                          <GenericFormComponent
                            type="SingleSelectAutoCompleteDropdown"
                            label={Translator.trans("toc.fields.technician")}
                            labelTooltip={Translator.trans(
                              "toc.messages.help.technician"
                            )}
                            name="technician"
                            fetchList={(value) =>
                              fetchFilteredPeople({
                                value,
                                groups: ["GG_SERVICE", "GG_SERVICE_AGENTS"],
                              })
                            }
                          />
                        </div>
                      </div>
                      <div className="row">
                        <div className="col-12">
                          <GenericFormComponent
                            type="SingleSelectAutoCompleteDropdown"
                            label={Translator.trans("toc.fields.customer")}
                            name="customer"
                            required
                            labelTooltip={Translator.trans(
                              "toc.messages.help.customer"
                            )}
                            fetchList={fetchCustomer}
                          />
                        </div>
                      </div>
                      <div className="row">
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectAutoCompleteDropdown"
                            label="Airport"
                            name="airport"
                            required
                            fetchList={fetchAirport}
                            labelTooltip={
                              airportMismatch && formType === "add"
                                ? Translator.trans(
                                    "toc.messages.errors.airport_mismatch"
                                  )
                                : undefined
                            }
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectPaginatedDropdown"
                            label={Translator.trans(
                              "toc.fields.sales_organisation_service"
                            )}
                            name="salesOrganisationService"
                            required
                            fetchPage={fetchServiceOrganisationPage}
                            labelTooltip={
                              serviceSsoMismatch && formType === "add"
                                ? Translator.trans(
                                    "toc.messages.errors.sso_mismatch"
                                  )
                                : undefined
                            }
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="Field"
                            disabled={!formValues?.equipmentRecord}
                            label={`${Translator.trans(
                              "service.follow_up_report.fields.hourmeter"
                            )} (${Translator.trans(
                              "toc.fields.hour_meter.last_known_value"
                            )} : ${
                              formValues?.equipmentRecord?.data?.data
                                ?.hourMeter ?? "N/A"
                            })`}
                            allowNumbersOnly
                            name="hourMeter"
                            required
                            labelTooltip={Translator.trans(
                              "toc.fields.hour_meter.tooltip"
                            )}
                          />
                        </div>
                      </div>
                      <div className="row">
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectStaticDropdown"
                            label={Translator.trans(
                              "toc.fields.indice_factor.label"
                            )}
                            name="indiceFactor"
                            list={IFACTOR_OPTIONS}
                            required
                            labelTooltip={Translator.trans(
                              "toc.fields.indice_factor.tooltip"
                            )}
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectDynamicDropdown"
                            label={Translator.trans(
                              "toc.fields.unit_operational_status"
                            )}
                            placeholder={Translator.trans(
                              "form.placeholder.search_by_name"
                            )}
                            name="unitOperationalStatus"
                            required
                            fetchList={fetchUnitOperationalStatusOptions}
                            onChange={(unitOperationalStatus) => {
                              if (
                                unitOperationalStatus.value !==
                                  "/unit_operational_statuses/MCF" &&
                                formValues?.indiceFactor?.value === "IF 1"
                              ) {
                                dispatch(
                                  change(
                                    formName,
                                    "indiceFactor",
                                    IFACTOR_OPTIONS[1]
                                  )
                                );
                              }
                            }}
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="Field"
                            label={Translator.trans(
                              "toc.fields.error_codes.label"
                            )}
                            name="errorCodes"
                            labelTooltip={Translator.trans(
                              "toc.fields.error_codes.tooltip"
                            )}
                          />
                        </div>
                      </div>
                      <div className="row">
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectDynamicDropdown"
                            label={Translator.trans(
                              "toc.fields.technician_on_call_type"
                            )}
                            placeholder={Translator.trans(
                              "form.placeholder.search_by_name"
                            )}
                            name="technicianOnCallType"
                            required
                            fetchList={fetchTechnicianOnCallTypeOptions}
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="SingleSelectDynamicDropdown"
                            label={Translator.trans(
                              "toc.fields.service_activity"
                            )}
                            placeholder={Translator.trans(
                              "form.placeholder.search_by_name"
                            )}
                            name="serviceActivity"
                            required
                            fetchList={fetchServiceActivityOptions}
                          />
                        </div>
                        <div className="col-4">
                          <GenericFormComponent
                            type="MutliSelectDynamicDropdown"
                            label={Translator.trans("toc.fields.tags.label")}
                            placeholder={Translator.trans(
                              "toc.fields.tags.placeholder"
                            )}
                            name="tags"
                            fetchList={fetchTechnicianOnCallTagOptions}
                          />
                        </div>
                      </div>
                      {/* Contacts block */}
                      <div className="row">
                        <div className="col-6 d-flex">
                          <div className="flex-grow-1 m-r">
                            <GenericFormComponent
                              type="SingleSelectStaticDropdown"
                              label={Translator.trans(
                                "toc.fields.main_contact.label"
                              )}
                              name="mainContact"
                              required
                              list={contactsList}
                              placeholder={Translator.trans(
                                "toc.fields.main_contact.placeholder"
                              )}
                              labelTooltip={Translator.trans(
                                "toc.fields.main_contact.tooltip"
                              )}
                              onInputChange={handleMainContactInputChange}
                              onChange={(
                                selectedContact: IDropdownItem | null
                              ) => {
                                if (!selectedContact) return;
                                const currentContacts: IDropdownItem[] =
                                  formValues?.contacts ?? [];
                                const filtered = currentContacts.filter(
                                  (contact) =>
                                    contact.value !== selectedContact.value
                                );
                                dispatch(
                                  change(
                                    formName,
                                    "contacts",
                                    filtered.length > 0 ? filtered : null
                                  )
                                );
                              }}
                            />
                          </div>
                          <div
                            className="technician_on_call_form__wrapper"
                            title={Translator.trans(
                              "toc.messages.help.create_contact"
                            )}
                          >
                            <button
                              className={`btn btn-sm btn-${
                                formValues?.customer !== null &&
                                canCreateExtranetUser
                                  ? "primary"
                                  : "secondary"
                              } btn-create-contact`}
                              onClick={() => setShowNewContactModal(true)}
                              type="button"
                              disabled={
                                formValues?.customer === null ||
                                canCreateExtranetUser === false
                              }
                            >
                              <i className="fa fa-fw fa-plus" />
                            </button>
                          </div>
                        </div>
                        <div className="col-6">
                          <GenericFormComponent
                            type="MutliSelectStaticDropdown"
                            label={Translator.trans(
                              "toc.fields.contacts.label"
                            )}
                            name="contacts"
                            list={otherContactsList}
                            placeholder={Translator.trans(
                              "toc.fields.contacts.placeholder"
                            )}
                            onInputChange={handleContactsInputChange}
                            onChange={(selectedContacts: IDropdownItem[]) => {
                              const mainContactValue =
                                formValues?.mainContact?.value;
                              if (
                                mainContactValue &&
                                selectedContacts.some(
                                  (c) => c.value === mainContactValue
                                )
                              ) {
                                dispatch(change(formName, "mainContact", null));
                              }
                            }}
                          />
                        </div>
                      </div>
                      {/* Third Party Block */}
                      <div className="row">
                        <div className="col-6">
                          <GenericFormComponent
                            type="Field"
                            label={Translator.trans(
                              "toc.fields.third_party_name.label"
                            )}
                            name="thirdPartyName"
                            labelTooltip={Translator.trans(
                              "toc.fields.third_party_name.tooltip"
                            )}
                          />
                        </div>
                        <div className="col-6">
                          <GenericFormComponent
                            type="Field"
                            label={Translator.trans(
                              "toc.fields.third_party_ref.label"
                            )}
                            name="thirdPartyRef"
                            labelTooltip={Translator.trans(
                              "toc.fields.third_party_ref.tooltip"
                            )}
                          />
                        </div>
                      </div>
                      <div className="row my-3 pt-0">
                        <div className="col-6 my-0 technician_on_call_form__switch_wrapper">
                          <GenericFormComponent
                            type="Switch"
                            label={Translator.trans(
                              "toc.fields.confidential.label"
                            )}
                            name="confidential"
                            labelTooltip={Translator.trans(
                              "toc.fields.confidential.tooltip"
                            )}
                          />

                          {shouldShowConfidentialReason && (
                            <GenericFormComponent
                              type="Field"
                              label={Translator.trans(
                                "toc.fields.confidential_reason.label"
                              )}
                              placeholder={Translator.trans(
                                "toc.fields.confidential_reason.placeholder"
                              )}
                              name="confidentialReason"
                              required
                              isTextArea
                            />
                          )}
                        </div>
                        {formType === "add" &&
                          canCreateCsr &&
                          formValues?.equipmentRecord && (
                            <div className="col-6 mt-1 mb-0 py-auto form-check form-switch technician_on_call_form__wrapper">
                              <GenericFormComponent
                                type="Switch"
                                label={Translator.trans(
                                  Translator.trans("toc.title.csr")
                                )}
                                name="canCreateCsr"
                                labelTooltip={Translator.trans(
                                  Translator.trans("toc.title.csr_helper")
                                )}
                                onChange={handleCreateNestedCsr}
                                fitContent
                              />
                              {createNestedCsr && (
                                <div className="col-md-8 mt-3">
                                  <NestedCustomerServiceRecordForm />
                                </div>
                              )}
                            </div>
                          )}
                      </div>
                    </div>
                    <div className="col-5 order-0 mt-3">
                      <Alert
                        variant="danger"
                        className="fw-bold technician_on_call_form__warning-message"
                      >
                        {Translator.trans("toc.messages.warning.wording")}
                      </Alert>
                      <div className="row">
                        <GenericFormComponent
                          type="Field"
                          required
                          label={Translator.trans("toc.fields.title")}
                          name="originalTitle"
                          labelTooltip={Translator.trans(
                            "toc.messages.help.length"
                          )}
                        />
                      </div>
                      <div className="row">
                        <GenericFormComponent
                          type="RichTextField"
                          label={Translator.trans("toc.fields.description")}
                          name="originalDescription"
                          labelTooltip={Translator.trans(
                            "toc.messages.help.length"
                          )}
                          required
                        />
                      </div>
                    </div>

                    <div className="row">
                      <div className="col text-end">
                        <button
                          className={`btn btn-${
                            valid ? "info" : "danger"
                          } m-b-xl`}
                          type="submit"
                          disabled={submitting || !valid}
                        >
                          <i className="fa fa-fw fa-save" />
                          &nbsp;Submit
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <Modal
              show={showNewContactModal}
              onHide={() => setShowNewContactModal(false)}
              size="lg"
            >
              <Modal.Header closeButton>
                <Modal.Title>
                  {Translator.trans("toc.messages.help.create_contact")}
                </Modal.Title>
              </Modal.Header>
              <Modal.Body>
                <ExtranetUserForm
                  customers={[formValues?.customer?.value ?? ""]}
                />
              </Modal.Body>
            </Modal>
          </>
        )}
        {formType === "add" && showFiles && !showSuccess && formValues.id && (
          <div className="row justify-content-center mt-2">
            <div
              className="col-8 alert alert-success alert-dismissible fade show"
              role="alert"
            >
              {Translator.trans("toc.messages.success.created", {
                id: formValues.id,
              })}
              <button
                type="button"
                className="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
              />
            </div>
            {formValues.nestedCustomerServiceRecord && (
              <div
                className={`col-8 alert alert-${
                  errorMessage ? "warning" : "success"
                } alert-dismissible fade show`}
                role="alert"
              >
                {!errorMessage
                  ? `${Translator.trans("csr.success.add")} (#${
                      formValues.nestedCustomerServiceRecord.id
                    })`
                  : `${Translator.trans("csr.errors.add")} : ${errorMessage}`}
                <button
                  type="button"
                  className="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                />
              </div>
            )}
            <div className="col-12">
              <TechnicianOnCallFiles data={formValues} />
            </div>
            <div className="col-12 ibox float-e-margins h-75 mt-3">
              <div className="text-center">
                <a
                  href={`/en/private/service/technician-on-calls/${formValues.id}/show`}
                  className="btn btn-info m-b-xl"
                >
                  &nbsp;{Translator.trans("button.finish")}
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}

interface IMappedProps {
  technicianOnCall: any;
}

const mapStateToProps = (state: RootState, ownProps: IProps & IMappedProps) => {
  const user = state.user.details;

  let initialValues: ITechnicianOnCallFormData = {
    confidential: false,
  };

  if (ownProps.technicianOnCall) {
    const toc = ownProps.technicianOnCall.assignee
      ? ownProps.technicianOnCall
      : { ...ownProps.technicianOnCall, assignee: user };

    initialValues = technicianOnCallFormFactory(toc);
  }

  return {
    initialValues,
    formType: ownProps.formType,
    formValues: getFormValues(formName)(state),
    showSuccess: state.technicianOnCall.showSuccess,
    showError: state.technicianOnCall.showError,
    errorMessage: state.technicianOnCall.errorMessage,
    showFiles: state.technicianOnCall.showFiles,
    canCreateCsr: state.user.canCreateCsr,
    canCreateExtranetUser: state.user.canCreateExtranetUser,
  };
};

export default connect(mapStateToProps)(
  reduxForm<ITechnicianOnCallFormData, IProps>({
    form: formName,
    enableReinitialize: true,
    keepDirtyOnReinitialize: true,
    validate,
  })(TechnicianOnCallForm)
);
