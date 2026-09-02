import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import { getCustomerDeliveryAddressesMapping } from "../../selectors/erp/erpSelectors";
import { RootState } from "../../store";

interface IProps {
  erpDeliveryAddressesList: any;
  label: any;
  name: any;
}

function ErpDeliveryAddressesSelect({
  erpDeliveryAddressesList,
  name = "deliveryAddress",
  label = "Delivery Address",
}: IProps) {
  return (
    <div className="form-control">
      {erpDeliveryAddressesList.length > 0 && (
        <div>
          <label className="col-form-label">{label}</label>
          <div>
            {erpDeliveryAddressesList.map(
              (erpDeliveryAddress: any, i: number) => (
                <div className="thumbnail" key={i}>
                  <div className="radio radio-primary">
                    <Field
                      component="input"
                      name={name}
                      id={name + i}
                      type="radio"
                      value={erpDeliveryAddress.value}
                    />
                    <label htmlFor={name + i}>
                      <p>{erpDeliveryAddress.label}</p>
                    </label>
                  </div>
                </div>
              )
            )}
          </div>
        </div>
      )}
    </div>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    erpDeliveryAddressesList: getCustomerDeliveryAddressesMapping(state),
  };
};

export default connect(mapStateToProps)(ErpDeliveryAddressesSelect);
