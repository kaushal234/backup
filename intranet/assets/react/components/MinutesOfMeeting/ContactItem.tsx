import React from "react";
import Translator from "bazinga-translator";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  contact: any;
}

function ContactItem({ contact }: IProps) {
  return (
    <div className="card-body">
      <div className="row">
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${contact}.firstName`}
            label={Translator.trans("directory.people.fields.firstname")}
            required
          />
        </div>
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${contact}.lastName`}
            label={Translator.trans("directory.people.fields.lastname")}
            required
          />
        </div>
      </div>
      <GenericFormComponent
        type="Field"
        name={`${contact}.mail`}
        label={Translator.trans("directory.people.fields.email")}
      />
      <div className="row">
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${contact}.company`}
            label={Translator.trans("directory.location.fields.company")}
            required
          />
        </div>
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${contact}.phone`}
            label={Translator.trans(
              "directory.location_contact.fields.telephone"
            )}
          />
        </div>
      </div>
    </div>
  );
}

export default ContactItem;
