import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { fetchItemMonologistic as fetchItemMonologisticAction } from "../../actions/part/itemsMonologisticAction";
import PartItemMonologisticSelect from "../Forms/PartItemMonologisticSelect";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  index: any;
  part: any;
  fetchItemMonologistic: any;
}

function PartItem(props: IProps) {
  const { index, part, fetchItemMonologistic } = props;
  return (
    <div className="card-body">
      <p className="text-end">
        <i>{Translator.trans("spare_parts_request.location_info")}</i>
      </p>
      <div className="row">
        <div className="col-md-8">
          <GenericFormComponent type="Field" name={`${part}.id`} hidden />
          <PartItemMonologisticSelect
            name={`${part}.partNumber`}
            required
            onChange={(event: any, value: any) => {
              fetchItemMonologistic(index, value.value);
            }}
          />
        </div>
      </div>
      <div className="row">
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${part}.quantity`}
            label={Translator.trans("fields.quantity")}
            required
            allowFloatsOnly
          />
        </div>
        <div className="col-md-6">
          <GenericFormComponent
            type="Field"
            name={`${part}.unitOfMeasure`}
            label={Translator.trans(
              "spare_parts_request.fields.unit_of_measure"
            )}
            required
          />
        </div>
      </div>
      <GenericFormComponent
        type="Field"
        name={`${part}.comment`}
        label={Translator.trans("fields.comment")}
        isTextArea
      />
    </div>
  );
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchItemMonologistic: (index: any, partNumber: any) =>
      dispatch(
        fetchItemMonologisticAction(
          index,
          partNumber,
          "spare_parts_request_form"
        )
      ),
  };
};

export default connect(() => ({}), mapDispatchToProps)(PartItem);
