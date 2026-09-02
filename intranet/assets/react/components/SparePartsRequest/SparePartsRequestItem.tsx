import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { fetchDeliveryAddresses as fetchDeliveryAddressesAction } from "../../actions/sparePartsRequest/deliveryAddressesActions";
import DeliveryAddressesSelect from "../Forms/SparePartsRequest/DeliveryAddressesSelect";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../Forms/Elements";
import SparePartsRequestNewAddress from "./SparePartsRequestNewAddress";
import LocationSelect from "../Forms/LocationsSelect";
import { AppDispatch } from "../../store";

interface IProps {
  fetchDeliveryAddresses: any;
  sparePartsRequest: any;
  formKey: any;
  index: any;
}

interface IState {
  sph: any;
  newAddress: any;
}

class SparePartsRequestItem extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      newAddress: false,
      sph: true,
    };
  }

  componentDidMount() {
    const { fetchDeliveryAddresses, sparePartsRequest } = this.props;
    fetchDeliveryAddresses(
      sparePartsRequest.airport["@id"],
      [sparePartsRequest.customer["@id"]],
      `${sparePartsRequest.airport["@id"]}-${sparePartsRequest.customer["@id"]}`
    );
    if (!sparePartsRequest.sph) {
      this.setState({ sph: false });
    }
  }

  render() {
    const { sparePartsRequest, formKey, index } = this.props;
    const { sph, newAddress } = this.state;
    const airport = sparePartsRequest.airport["@id"];
    const customer = sparePartsRequest.customer["@id"];
    const equipmentRecords: Array<any> = [];
    sparePartsRequest.equipmentRecords.map((er: any) =>
      equipmentRecords.push(`ER#${er.serialNumber}`)
    );
    const choices: any = {
      false: Translator.trans(
        "spare_parts_request.fields.existing_delivery_address"
      ),
      true: Translator.trans(
        "spare_parts_request.fields.submit_new_delivery_address"
      ),
    };
    return (
      <div className="card-body">
        <p className="text-center" style={{ fontWeight: "bold" }}>
          {sparePartsRequest.customer.name} - {sparePartsRequest.airport.code}/
          {sparePartsRequest.airport.cityName} (
          {Translator.trans("spare_parts_request.sb.er_involved")} :{" "}
          {equipmentRecords.join(", ")})
        </p>
        <hr />
        {!sph && (
          <div className="row">
            <div className="col-md-3">
              <LocationSelect
                component={renderReactVerticalSelect}
                required
                label="SPH"
                name={`${formKey}.sph`}
                placeholder="Select a SPH"
                locationListName="sparePartsHubs"
              />
            </div>
          </div>
        )}
        <div className="row">
          <div className="col-md-5">
            <Field
              component={renderVerticalSelect}
              name={`${formKey}.newAddress`}
              onChange={() => this.setState({ newAddress: !newAddress })}
            >
              {Object.keys(choices).map((key) => (
                <option value={key} key={key}>
                  {choices[key]}
                </option>
              ))}
            </Field>
          </div>
        </div>
        {!newAddress && (
          <DeliveryAddressesSelect
            name={`${formKey}.deliveryAddress`}
            discriminator={`${airport}-${customer}`}
          />
        )}
        {newAddress && (
          <SparePartsRequestNewAddress
            displayAirport={false}
            formKey={formKey}
            form="sb_spare_parts_request_form"
            index={index}
            discriminator={`${airport}-${customer}`}
          />
        )}
      </div>
    );
  }
}

const mapStateToProps = () => {
  return {};
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchDeliveryAddresses: (
      airport: any,
      customers: any,
      discriminator: any
    ) =>
      dispatch(fetchDeliveryAddressesAction(airport, customers, discriminator)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(SparePartsRequestItem);
