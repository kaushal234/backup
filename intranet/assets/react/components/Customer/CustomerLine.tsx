import React from "react";
import { connect } from "react-redux";
import _ from "lodash";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderInlineTextarea } from "../Forms/Elements";
import { updateCustomerWatchList as updateCustomerWatchListAction } from "../../actions/customer/customersActions";
import Loader from "../Loader";
import { AppDispatch } from "../../store";

interface IProps {
  customer: any;
  name: any;
  form: any;
  customerIndex: any;
  updateCustomerWatchList: any;
}

function CustomerLine({
  customer,
  name,
  form,
  customerIndex,
  updateCustomerWatchList,
}: IProps) {
  return customer.softDeleted !== 1 ? (
    <tr>
      <td>
        <a
          href={`/en/private/sales/customers/${customer.id}/show`}
          target="_blank"
          rel="noreferrer"
        >
          {customer.id}
        </a>
      </td>
      <td>{customer.name}</td>
      <td>{_.get(customer, "type.name")}</td>
      <td>{_.get(customer, "country.name")}</td>
      <td>
        <Field
          name={`${name}.watchListReason`}
          component={renderInlineTextarea as any}
          style={{ resize: "vertical" }}
          rows={4}
        />
      </td>
      <td>
        {customer.saving === 1 && (
          <Loader
            style={{ display: "block", width: 20 }}
            childStyle={{ padding: 0, height: 20 }}
          />
        )}
        {customer.saving !== 1 && (
          <button
            className="btn btn-success"
            onClick={() =>
              updateCustomerWatchList(customer, customerIndex, true, form)
            }
          >
            {Translator.trans("button.save")}
          </button>
        )}
      </td>
      <td className="text-end">
        {customer.deleting === 1 && (
          <Loader
            style={{ display: "block", width: 20 }}
            childStyle={{ padding: 0, height: 20 }}
          />
        )}
        {customer.deleting !== 1 && (
          <button
            className="btn btn-danger"
            onClick={() =>
              updateCustomerWatchList(customer, customerIndex, false, form)
            }
          >
            {Translator.trans("customers.fields.watch_list_remove")}
          </button>
        )}
      </td>
    </tr>
  ) : (
    <div />
  );
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    updateCustomerWatchList: (
      customer: any,
      customerIndex: any,
      onWatchList: any,
      form: any
    ) => {
      dispatch(
        updateCustomerWatchListAction(
          customer["@id"],
          customerIndex,
          onWatchList,
          onWatchList ? customer.watchListReason : null,
          form
        )
      );
    },
  };
};

export default connect(() => ({}), mapDispatchToProps)(CustomerLine);
