import React from "react";
import { FieldArray, reduxForm } from "redux-form";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getSalesCustomersMapping } from "../../selectors/customer/customersSelector";
import CustomersArray from "../../components/Customer/CustomersArray";
import { RootState } from "../../store";

const formConfiguration = {
  form: "customers_watch_list_quick_edit",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
};

function CustomersWatchListQuickEdit() {
  return (
    <div className="ibox float-e-margins">
      <div className="ibox-title">
        <h5>{Translator.trans("customers.fields.watch_list")}</h5>
      </div>
      <div className="ibox-content">
        <div className="">
          <table className="table table-hover report-table table-responsive">
            <thead>
              <tr>
                <th>{Translator.trans("customers.fields.id")}</th>
                <th>{Translator.trans("customers.fields.name")}</th>
                <th>{Translator.trans("customers.fields.type")}</th>
                <th>{Translator.trans("customers.fields.address_country")}</th>
                <th>
                  {Translator.trans("customers.fields.watch_list_reason")}
                </th>
                <th />
                <th />
              </tr>
            </thead>
            <tbody>
              <FieldArray
                rerenderOnEveryChange
                name="customers"
                component={CustomersArray}
                form={formConfiguration.form}
              />
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    initialValues: { customers: getSalesCustomersMapping(state) },
  };
};

export default connect(mapStateToProps)(
  reduxForm(formConfiguration)(CustomersWatchListQuickEdit)
);
