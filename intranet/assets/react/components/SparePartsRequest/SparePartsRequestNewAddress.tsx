import React from "react";
import Translator from "bazinga-translator";
import { connect } from "react-redux";
import ExtranetUserAsyncSelect from "../Forms/ExtranetUserAsyncSelect";
import AirportSelect from "../Forms/AirportSelect";
import CountrySelect from "../Forms/CountriesSelect";
import { fetchContact as fetchContactAction } from "../../actions/extranetUser/extranetUserActions";
import { fetchAirport as fetchAirportAction } from "../../actions/apc/airportsActions";
import { SPR_FETCH_AIRPORT } from "../../constants";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  form: any;
  formKey?: any;
  index?: any;
  discriminator?: any;
  displayAirport?: any;
  fetchContact: any;
  fetchAirport: any;
}

function SparePartsRequestNewAddress(props: IProps) {
  const {
    form,
    formKey,
    index,
    discriminator,
    displayAirport = true,
    fetchContact,
    fetchAirport,
  } = props;
  return (
    <div>
      <hr />
      <div className="row">
        <div className="col-md-3">
          <ExtranetUserAsyncSelect
            name={formKey ? `${formKey}.contact` : "contact"}
            extranetUsers="extranetUsers"
            discriminator={discriminator}
            async={false}
            onChange={(value: any) => {
              fetchContact(value.value, form, index);
            }}
          />
        </div>
        {displayAirport && (
          <div className="col-md-3">
            <AirportSelect
              name={formKey ? `${formKey}.airport` : "airport"}
              onChange={(value: any) => {
                fetchAirport(value.value, form);
              }}
            />
          </div>
        )}
        <div className="col-md-3">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.lastname` : "lastname"}
            required
            label={Translator.trans("contacts.fields.lastname")}
          />
        </div>
        <div className="col-md-3">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.firstname` : "firstname"}
            required
            label={Translator.trans("contacts.fields.firstname")}
          />
        </div>
      </div>
      <div className="row">
        <div className="col-md-2">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.company` : "company"}
            required
            label={Translator.trans("account_receivable.fields.company")}
          />
        </div>
        <div className="col-md-4">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.street1` : "street1"}
            required
            label={Translator.trans("contacts.fields.address_street_1")}
          />
        </div>
        <div className="col-md-4">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.street2` : "street2"}
            label={Translator.trans("contacts.fields.address_street_2")}
          />
        </div>
        <div className="col-md-2">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.phone` : "phone"}
            label={Translator.trans(
              "directory.location_contact.fields.telephone"
            )}
            required
          />
        </div>
      </div>
      <div className="row">
        <div className="col-md-2">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.postalCode` : "postalCode"}
            required
            label={Translator.trans("contacts.fields.address_postal_code")}
          />
        </div>
        <div className="col-md-2">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.town` : "town"}
            label={Translator.trans("contacts.fields.address_town")}
          />
        </div>
        <div className="col-md-2">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.city` : "city"}
            required
            label={Translator.trans("contacts.fields.address_city")}
          />
        </div>
        <div className="col-md-3">
          <GenericFormComponent
            type="Field"
            name={formKey ? `${formKey}.state` : "state"}
            label={Translator.trans("contacts.fields.address_state")}
          />
        </div>
        <div className="col-md-3">
          <CountrySelect
            name={formKey ? `${formKey}.country` : "country"}
            required
            label={Translator.trans("contacts.fields.country")}
          />
        </div>
      </div>
    </div>
  );
}

const mapStateToProps = () => {
  return {};
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchAirport: (airport: any, form: any) =>
      dispatch(fetchAirportAction(airport, form, SPR_FETCH_AIRPORT)),
    fetchContact: (contact: any, form: any, index: any) =>
      dispatch(fetchContactAction(contact, form, index)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(SparePartsRequestNewAddress);
