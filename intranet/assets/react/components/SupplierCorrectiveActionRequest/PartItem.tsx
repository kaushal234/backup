import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import PartItemSelect from "../Forms/PartItemSelect";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  part: any;
  erp: any;
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
    const { part } = this.props;
    const { erp } = this.state;
    return (
      <div className="card-body">
        <div className="row">
          <div className="col-md-8">
            <PartItemSelect name={`${part}.partNumber`} required erp={erp} />
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
      </div>
    );
  }
}

export default connect(
  () => ({}),
  () => ({})
)(PartItem);
