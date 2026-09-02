import React, { useEffect, useState } from "react";
import { change, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { Col, Row } from "react-bootstrap";
import { validate } from "../../model/form/contact_campaign_form/validation";
import { IContactCampaignFormData } from "../../types/IContactCampaignFormData";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { CONTACT_CAMPAIGN_STATUS_OPTIONS } from "../../constants/constants";
import { fetchBusinessUnit } from "../../utils/dropdown/businessUnit";
import { fetchCustomer } from "../../utils/dropdown/customer";
import { fetchLocation } from "../../utils/dropdown/location";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import ContactTable from "../ContactTable/ContactTable";

const formName = "contact_campaign";

export interface IContactCampaignFormProps {
  isEdit?: boolean;
  onSubmit: (values: IContactCampaignFormData) => void;
}

type IWrappedProps = IContactCampaignFormProps &
  InjectedFormProps<IContactCampaignFormData, IContactCampaignFormProps>;

function ContactCampaignForm(props: IWrappedProps) {
  const {
    submitting,
    handleSubmit,
    submitFailed,
    invalid,
    onSubmit,
    isEdit,
    initialValues,
  } = props;
  const dispatch = useAppDispatch();

  const formValues = useAppSelector((state) => state.form[formName]?.values);

  const customerFilter = formValues?.customerFilter || [];
  const locationFilter = formValues?.locationFilter || [];

  const startedAt = formValues?.startedAt;
  const endedAt = formValues?.endedAt;

  const [selectedContacts, setSelectedContacts] = useState(
    initialValues?.contacts || []
  );

  useEffect(() => {
    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);

    dispatch(change(formName, "startedAt", today));
    dispatch(change(formName, "endedAt", tomorrow));
    dispatch(change(formName, "status", CONTACT_CAMPAIGN_STATUS_OPTIONS[0]));
  }, []);

  const getMaxStartedAt = (endDate?: Date | null) => {
    if (!endDate) return null;
    const maxDate = new Date(endDate);
    maxDate.setDate(maxDate.getDate() - 1);
    return maxDate;
  };

  const getMinEndedAt = (startDate?: Date | null) => {
    if (!startDate) return null;
    const minDate = new Date(startDate);
    minDate.setDate(minDate.getDate() + 1);
    return minDate;
  };

  const handleFormSubmit = (values: IContactCampaignFormData) => {
    const finalValues = {
      ...values,
      contacts: selectedContacts,
    };

    onSubmit(finalValues);
  };

  return (
    <form noValidate onSubmit={handleSubmit(handleFormSubmit)} className="card">
      <div className="card-header">
        <h3>
          {isEdit
            ? Translator.trans("contact_campaign.title.contact_campaign_edit")
            : Translator.trans("contact_campaign.title.contact_campaign_add")}
        </h3>
      </div>
      <Row className="px-3">
        <Col md={6}>
          <GenericFormComponent
            type="Field"
            label={Translator.trans("contact_campaign.name.label")}
            placeholder={Translator.trans("contact_campaign.name.placeholder")}
            name="name"
            required
          />
        </Col>
        <Col md={3}>
          <GenericFormComponent
            type="DatePicker"
            label={Translator.trans("contact_campaign.started_at.label")}
            name="startedAt"
            required
            min={new Date()}
            max={getMaxStartedAt(endedAt)}
          />
        </Col>
        <Col md={3}>
          <GenericFormComponent
            type="DatePicker"
            label={Translator.trans("contact_campaign.ended_at.label")}
            name="endedAt"
            required
            min={getMinEndedAt(startedAt)}
          />
        </Col>
      </Row>
      <Row className="px-3">
        <Col md={3}>
          <GenericFormComponent
            type="SingleSelectStaticDropdown"
            label={Translator.trans("contact_campaign.status.label")}
            name="status"
            list={CONTACT_CAMPAIGN_STATUS_OPTIONS}
            required
          />
        </Col>
        <Col md={9}>
          <GenericFormComponent
            fetchList={fetchBusinessUnit}
            triggerAtChar={0}
            type="MutliSelectAutoCompleteDropdown"
            label={Translator.trans("contact_campaign.business_units.label")}
            placeholder={Translator.trans(
              "contact_campaign.business_units.placeholder"
            )}
            name="businessUnits"
            required
          />
        </Col>
      </Row>
      <Row className="px-3">
        <Col>
          <GenericFormComponent
            type="Field"
            label={Translator.trans("contact_campaign.description.label")}
            placeholder={Translator.trans(
              "contact_campaign.description.placeholder"
            )}
            name="description"
            required
            isTextArea
          />
        </Col>
      </Row>
      <Row className="px-3">
        <Col md={6}>
          <GenericFormComponent
            fetchList={fetchCustomer}
            triggerAtChar={0}
            type="MutliSelectAutoCompleteDropdown"
            label={Translator.trans("contact_campaign.customer.label")}
            placeholder={Translator.trans(
              "contact_campaign.customer.placeholder"
            )}
            name="customerFilter"
          />
        </Col>
        <Col md={6}>
          <GenericFormComponent
            fetchList={fetchLocation}
            triggerAtChar={0}
            type="MutliSelectAutoCompleteDropdown"
            label={Translator.trans("contact_campaign.location.label")}
            placeholder={Translator.trans(
              "contact_campaign.location.placeholder"
            )}
            name="locationFilter"
          />
        </Col>
      </Row>
      <Row className="px-3">
        <Col>
          <ContactTable
            customerFilter={customerFilter}
            locationFilter={locationFilter}
            onSelectedContactsChange={setSelectedContacts}
            selectedContacts={selectedContacts}
          />
        </Col>
      </Row>
      <Row className="px-3 pb-3">
        <Col sm="12" className="text-end">
          <button
            className="btn btn-info mt-3"
            type="submit"
            disabled={submitting || (submitFailed && invalid)}
          >
            {Translator.trans("contact_campaign.button.submit")}
          </button>
        </Col>
      </Row>
    </form>
  );
}

export default reduxForm<IContactCampaignFormData, IContactCampaignFormProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ContactCampaignForm);
