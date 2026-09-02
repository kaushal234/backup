import React from "react";
import { connect } from "react-redux";
import {
  autofill,
  FieldArray,
  formValueSelector,
  InjectedFormProps,
  reduxForm,
} from "redux-form";
import _ from "lodash";
import { getSalesForecastsMapping } from "../../selectors/sfr/salesForecastSelector";
import validate from "../../model/form/sfr_quick_edit/validation";
import SalesForecastsArray from "../../components/SFR/SalesForecastsArray";
import { updateSalesForecasts as updateSalesForecastsAction } from "../../actions/sfr/sfrActions";
import SweetAlert from "../../utils/SweetAlert";
import Loader from "../../components/Loader";
import { hideSuccessAlert } from "../../actions/genericActions";
import { AppDispatch, RootState } from "../../store";

type IFormData = any;

interface IProps {
  showSuccessMessage: any;
  showSuccess: any;
  updateSalesForecasts: any;
  hideSuccessMessage: any;
  loading: any;
  redirectFlag: any;
}

interface IState {
  showSuccessMessage?: any;
  previousWindowKeyDown: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class SalesForecastQuickEditForm extends React.Component<
  IWrappedProps,
  IState
> {
  constructor(props: IWrappedProps) {
    super(props);
    this.onCloseSweetAlert = this.onCloseSweetAlert.bind(this);
    this.onSubmit = this.onSubmit.bind(this);
    this.state = {
      previousWindowKeyDown: null,
    };
  }

  static getDerivedStateFromProps(props: IWrappedProps, state: IState) {
    const returnState: any = {
      previousWindowKeyDown: null,
    };

    if (
      state.showSuccessMessage &&
      props.showSuccessMessage !== state.showSuccessMessage
    ) {
      returnState.previousWindowKeyDown = window.onkeydown;
    }

    return returnState;
  }

  componentDidUpdate(prevProps: IWrappedProps) {
    const { showSuccess } = this.props;
    const { previousWindowKeyDown } = this.state;
    if (showSuccess !== prevProps.showSuccess && showSuccess === false) {
      window.onkeydown = previousWindowKeyDown;
    }
  }

  onSubmit(values: any) {
    const { updateSalesForecasts, initialValues } = this.props;
    const dirtySalesForecasts = (values.salesForecasts || []).filter(
      (value: any, key: any) => {
        return !_.isEqual(value, initialValues.salesForecasts[key]);
      }
    );
    const apiErrors = { salesForecasts: [] };

    updateSalesForecasts(dirtySalesForecasts, apiErrors);
  }

  onCloseSweetAlert() {
    const { hideSuccessMessage } = this.props;
    hideSuccessMessage();
  }

  render() {
    const {
      submitting,
      loading,
      valid,
      handleSubmit,
      showSuccess,
      redirectFlag,
    } = this.props;

    if (redirectFlag && !showSuccess) {
      window.location.href = `/en/private/sales/sales-forecasts/gantt`;
      return <Loader />;
    }

    return (
      <div>
        <SweetAlert
          show={showSuccess}
          title="Saved"
          type="success"
          onConfirm={() => this.onCloseSweetAlert()}
          onClose={() => this.onCloseSweetAlert()}
        />
        <form
          onSubmit={handleSubmit(this.onSubmit.bind(this))}
          style={{ opacity: submitting || loading ? 0.1 : 1 }}
        >
          <div className="">
            <table className="table table-hover report-table table-responsive">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Status</th>
                  <th>Buyer - User</th>
                  <th>Country</th>
                  <th>Factory - Model</th>
                  <th>Sales Date</th>
                  <th>Qty</th>
                  <th>Cust. %</th>
                  <th>TLD %</th>
                  <th>Update Linked</th>
                  <th>Restricted communication</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <FieldArray
                  name="salesForecasts"
                  component={SalesForecastsArray as any}
                />
              </tbody>
            </table>
          </div>
          <div className="row">
            <div className="col-xs-12 text-end">
              <button
                className={`btn btn-${valid ? "info" : "danger"} m-b-xl`}
                type="submit"
                disabled={submitting || !valid}
              >
                Save
              </button>
            </div>
          </div>
        </form>
      </div>
    );
  }
}

const formConfiguration = {
  form: "sfr_quick_edit",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
  validate,
};

const selector = formValueSelector(formConfiguration.form);

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    updateSalesForecasts: (salesForecasts: any, errors: any) =>
      dispatch(
        updateSalesForecastsAction(
          salesForecasts,
          errors,
          formConfiguration.form
        )
      ),
    hideSuccessMessage: () => {
      dispatch(autofill("sfr_quick_edit", `redirectFlag`, true));
      dispatch(hideSuccessAlert("SFR"));
    },
  };
};

const mapStateToProps = (props: RootState) => {
  const initialValues = { salesForecasts: getSalesForecastsMapping(props) };
  const redirectFlag = !!selector(props, "redirectFlag");
  const showSuccess = !!props.sfr.showSuccess;
  return {
    initialValues,
    redirectFlag,
    showSuccess,
    salesForecasts: getSalesForecastsMapping(props),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(SalesForecastQuickEditForm));
