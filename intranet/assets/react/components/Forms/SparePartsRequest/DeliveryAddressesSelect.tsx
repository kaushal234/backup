import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import { getDeliveryAddressesMapping } from "../../../selectors/sparePartsRequest/deliveryAddressesSelector";
import { RootState } from "../../../store";

interface IProps {
  getDeliveryAddresses: any;
  label?: any;
  name?: any;
  discriminator?: any;
}

function DeliveryAddressesSelect({
  getDeliveryAddresses,
  name = "deliveryAddress",
  label = "Delivery Address",
  discriminator = "discr",
}: IProps) {
  const deliveryAddresses = getDeliveryAddresses(discriminator)
    .slice()
    .sort((a: any, b: any) => {
      const dateA = a.lastUsedAt ? new Date(a.lastUsedAt).getTime() : 0;
      const dateB = b.lastUsedAt ? new Date(b.lastUsedAt).getTime() : 0;
      return dateB - dateA;
    });
  return (
    <div className="form-control">
      {deliveryAddresses.length > 0 && (
        <div>
          <label className="col-form-label">{label}</label>
          <div>
            {deliveryAddresses.map((deliveryAddress: any, i: number) => (
              <div className="thumbnail" key={i}>
                <div className="radio radio-primary">
                  <Field
                    component="input"
                    name={name}
                    id={name + i}
                    type="radio"
                    value={deliveryAddress.value}
                  />
                  <label htmlFor={name + i}>
                    <p>
                      <a
                        href={`/en/private/sales/contacts/${deliveryAddress.contact.id}/show`}
                      >
                        {deliveryAddress.contact.lastname},{" "}
                        {deliveryAddress.contact.firstname} (ID#
                        {deliveryAddress.contact.id})
                      </a>
                      , {deliveryAddress.label}
                      {". "}
                      {deliveryAddress.firstname} {deliveryAddress.lastname}{" "}
                      {deliveryAddress.phone}{" "}
                      {deliveryAddress.lastUsedAt && (
                        <i>
                          Last used:{" "}
                          {new Date(
                            deliveryAddress.lastUsedAt
                          ).toLocaleDateString()}
                        </i>
                      )}
                    </p>
                  </label>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    getDeliveryAddresses: (discriminator: any) =>
      getDeliveryAddressesMapping(state, discriminator),
  };
};

export default connect(mapStateToProps)(DeliveryAddressesSelect);
