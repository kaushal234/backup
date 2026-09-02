import React from "react";
import { connect } from "react-redux";
import { getSalesCustomersSelectMapping } from "../../selectors/customer/customersSelector";
import {
  clearSalesCustomers as clearSalesCustomersAction,
  createCustomerInForm as createCustomerInFormAction,
  fetchSalesCustomers as fetchSalesCustomersAction,
} from "../../actions/customer/customersActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  createCustomerInForm: any;
  customers: any;
  customersListIsLoading: any;
  fetchSalesCustomers: any;
  required: any;
  label?: any;
  name?: any;
  placeholder?: any;
  simpleList?: any;
  onChange?: any;
  clearSalesCustomers: any;
  customerNotFound: any;
  showSuccess: any;
  showError: any;
  inputName: any;
  formName?: any;
  adder?: any;
  showHidden?: any;
  showActive?: any;
  disabled?: any;
  isMulti?: any;
  labelTooltip?: string;
}

interface IState {
  input: any;
}

class CustomersSelect extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      input: null,
    };

    this.createCustomer = this.createCustomer.bind(this);
  }

  createCustomer(input: any, inputName: any, form: any) {
    const { createCustomerInForm } = this.props;
    const customer = {
      name: input,
    };
    createCustomerInForm(customer, inputName, form);
  }

  render() {
    const {
      customers,
      customersListIsLoading,
      fetchSalesCustomers,
      onChange,
      clearSalesCustomers,
      customerNotFound,
      showSuccess,
      showError,
      inputName,
      formName,
      required = false,
      label = "eCustomer",
      name = "customer",
      placeholder = "Type to search",
      adder = false,
      showHidden = false,
      showActive = false,
      simpleList = false,
      isMulti,
      labelTooltip,
    } = this.props;

    const { input } = this.state;
    return (
      <div>
        {adder && customerNotFound && inputName === name && (
          <div className="float-end">
            <a
              onClick={(e) => {
                e.preventDefault();
                this.createCustomer(input, inputName, formName);
              }}
              href="#"
              className="label label-info"
            >
              <i className="fa fa-fw fa-plus" />
              &nbsp;Add eCustomer
            </a>
          </div>
        )}
        {showSuccess && inputName === name && (
          <div className="float-end">
            <a style={{ color: "green" }}>
              eCustomer added&nbsp;
              <i className="fa fa-fw fa-check" />
            </a>
          </div>
        )}
        {showError && inputName === name && (
          <div className="float-end">
            <a style={{ color: "red" }}>
              Creation failed&nbsp;
              <i className="fa fa-fw fa-times" />
            </a>
          </div>
        )}
        <GenericFormComponent
          type={
            isMulti ? "MutliSelectStaticDropdown" : "SingleSelectStaticDropdown"
          }
          list={customers}
          isLoadingExternally={customersListIsLoading}
          {...this.props}
          name={name}
          label={label}
          labelTooltip={labelTooltip}
          required={required}
          placeholder={placeholder}
          onChange={simpleList || onChange ? onChange : clearSalesCustomers}
          onInputChange={(newInput: any) => {
            if (!newInput || newInput.length < 3) {
              return;
            }
            this.setState({ input: newInput });
            fetchSalesCustomers(newInput, name, showHidden, showActive);
          }}
        />
      </div>
    );
  }
}

const mapStateToProps = (state: RootState) => {
  const { customer } = state;
  return {
    customers: getSalesCustomersSelectMapping(state),
    customersListIsLoading: customer.customersListIsLoading,
    customerNotFound: customer.customerNotFound,
    showSuccess: customer.showSuccess,
    showError: customer.showError,
    inputName: customer.inputName,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchSalesCustomers: (
      search: any,
      name: any,
      showHidden: any,
      showActive: any
    ) => {
      dispatch(fetchSalesCustomersAction(search, name, showHidden, showActive));
    },
    clearSalesCustomers: () => {
      dispatch(clearSalesCustomersAction());
    },
    createCustomerInForm: (customer: any, inputName: any, form: any) => {
      dispatch(createCustomerInFormAction(customer, inputName, form));
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(CustomersSelect);
