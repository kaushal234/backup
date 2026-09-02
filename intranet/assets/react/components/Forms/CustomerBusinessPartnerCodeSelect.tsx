import React from "react";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  businessPartnerCodesChoice: any;
  required?: any;
  label?: any;
  name?: any;
  placeholder?: any;
}

class CustomerBusinessPartnerCodeSelect extends React.Component<IProps> {
  constructor(props: IProps) {
    super(props);
    this.state = {};
  }

  render() {
    const {
      required = true,
      label = "Business Partner Code",
      name = "customerBusinessPartnerCode",
      placeholder = "Type to search",
      businessPartnerCodesChoice,
    } = this.props;
    return (
      <div>
        <GenericFormComponent
          type="SingleSelectStaticDropdown"
          name={name}
          label={label}
          list={businessPartnerCodesChoice}
          required={required}
          placeholder={placeholder}
        />
      </div>
    );
  }
}

export default CustomerBusinessPartnerCodeSelect;
