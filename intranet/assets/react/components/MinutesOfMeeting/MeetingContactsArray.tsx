import React from "react";
import ContactItem from "./ContactItem";

interface IProps {
  fields: any;
}

const renderContactForm = ({ fields }: IProps) => {
  return (
    <div>
      <div>
        <button
          className="btn btn-info"
          type="button"
          onClick={() => fields.push({})}
        >
          <i className="fa fa-fw fa-plus" />
          &nbsp;Add Punctual Contact
        </button>
      </div>
      <div>
        {fields.map((contact: any, index: number) => (
          <div className="card " key={index}>
            <div className="card-header">
              <button
                className="btn btn-sm btn-danger float-end"
                type="button"
                title="Remove Contact"
                onClick={() => fields.remove(index)}
              >
                <i className="fa fa-fw fa-trash" />
              </button>
              <h4>Contact #{index + 1}</h4>
            </div>
            <ContactItem contact={contact} />
          </div>
        ))}
      </div>
    </div>
  );
};

export default renderContactForm;
