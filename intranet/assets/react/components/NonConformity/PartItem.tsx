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
            <Field
              component={renderVerticalSelect}
              name={`${part}.reference`}
              label={Translator.trans("market_intelligence.fields.type")}
            >
              <option value="" />
              {[
                "CUST",
                "DO",
                "OTHER",
                "PART",
                "PO",
                "PROD",
                "SN",
                "WELD",
                "WHSE",
                "WO",
              ].map((type) => (
                <option value={type} key={type}>
                  {type}
                </option>
              ))}
            </Field>
          </div>
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.referenceNumber`}
              label={Translator.trans("non_conformity.fields.reference_number")}
            />
          </div>
          <div className="col-md-4">
            <GenericFormComponent
              type="Field"
              name={`${part}.serialNumber`}
              label={Translator.trans("demo.fields.serial_number")}
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
