import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import PartItemSelect from "../Forms/PartItemSelect";
import { fetchItem as fetchItemAction } from "../../actions/erp/itemsAction";
import { renderVerticalSelect } from "../Forms/Elements";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  erp: any;
  index: any;
  part: any;
  fetchItem: any;
}

interface IState {
  erp: any;
}

class PartItem extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      erp: null,
    };
  }

  componentDidMount() {
    const { erp } = this.props;
    this.setState({ erp });
  }

  render() {
    const { index, part, fetchItem } = this.props;
    const { erp } = this.state;
    return (
      <div className="card-body">
        <div className="row">
          <div className="col-md-8">
            <PartItemSelect
              name={`${part}.partNumber`}
              required
              erp={erp}
              onChange={(event: any, value: any) => {
                fetchItem(index, value.value, erp);
              }}
            />
          </div>
          <div className="col-md-2">
            <GenericFormComponent
              type="Field"
              name={`${part}.unitOfMeasure`}
              label={Translator.trans(
                "spare_parts_request.fields.unit_of_measure"
              )}
              required
            />
          </div>
          <div className="col-md-2">
            <GenericFormComponent
              type="Field"
              name={`${part}.quantity`}
              label={Translator.trans("fields.quantity")}
              required
              allowFloatsOnly
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.serialNumber`}
              label={Translator.trans("demo.fields.serial_number")}
            />
          </div>
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.vendorSerialNumber`}
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.vendor_serial_number"
              )}
            />
          </div>
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.vendorPartNumber`}
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.vendor_part_number"
              )}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.standardCost`}
              allowFloatsOnly
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.standard_cost"
              )}
            />
          </div>
          <div className="col-md-4">
            <Field
              component={renderVerticalSelect}
              name={`${part}.failureType`}
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.failure_type"
              )}
            >
              <option value="" />
              {[
                "HYDRAULIC",
                "ELECTRICAL",
                "MECHANICAL",
                "PLUMBING",
                "PNEUMATIC",
                "REFRIGERATION",
              ].map((type) => (
                <option value={type} key={type}>
                  {type}
                </option>
              ))}
            </Field>
          </div>
          <div className="col-md-4">
            <Field
              component={renderVerticalSelect}
              name={`${part}.failureSystem`}
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.failure_system"
              )}
            >
              <option value="" />
              {[
                "BRAKING",
                "HYDRAULIC",
                "PNEUMATIC",
                "REFRIGERATION",
                "ELEC CONTROL",
                "ENGINE",
                "TRANSMISSION",
                "SUSPENSION",
                "STRUCTURAL",
                "CHASSIS",
                "BODY",
                "COMPRESSOR",
                "GENERATOR",
              ].map((type) => (
                <option value={type} key={type}>
                  {type}
                </option>
              ))}
            </Field>
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <GenericFormComponent
              type="Checkbox"
              name={`${part}.ship`}
              label={Translator.trans("vendor_warranty_claim.part.fields.ship")}
            />
          </div>
          <div className="col-md-6">
            <GenericFormComponent
              type="Field"
              name={`${part}.receivedQuantity`}
              label={Translator.trans(
                "vendor_warranty_claim.part.fields.received_quantity"
              )}
            />
          </div>
        </div>
      </div>
    );
  }
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchItem: (index: any, partNumber: any, erp: any) =>
      dispatch(fetchItemAction(index, partNumber, erp, "parts_form")),
  };
};

export default connect(() => ({}), mapDispatchToProps)(PartItem);
