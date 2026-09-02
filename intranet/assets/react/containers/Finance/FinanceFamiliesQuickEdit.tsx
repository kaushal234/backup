import React from "react";
import { connect } from "react-redux";
import { FieldArray, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import {
  getLocationMapping,
  getLocationsMapping,
} from "../../selectors/location/locationSelectors";
import { getFinanceFamiliesMapping } from "../../selectors/finance/financeFamilySelector";
import FinanceFamiliesArray from "../../components/Finance/FinanceFamiliesArray";
import LocationSelect from "../../components/Forms/LocationsSelect";
import { renderReactVerticalSelect } from "../../components/Forms/Elements";
import { getUserDetails } from "../../selectors/user/userSelectors";
import { RootState } from "../../store";

type IFormData = any;

interface IProps {
  factories: any;
  isGrantedCreate: any;
}

interface IState {
  sso: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class FinanceFamiliesQuickEdit extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps, context: any) {
    super(props, context);
    this.state = {
      sso: props.initialValues.sso,
    };
  }

  render() {
    const { factories, isGrantedCreate } = this.props;
    const { sso } = this.state;
    return (
      <div>
        <div className="visible-lg">
          <div className="row">
            <div className="col-md-4">
              <LocationSelect
                component={renderReactVerticalSelect}
                name="sso"
                label="Select SSO"
                onChange={(event: any, newSso: any) => {
                  this.setState({ sso: newSso });
                }}
                required={false}
                placeholder="Select a SSO"
                locationListName="ssos"
              />
            </div>
          </div>
          {sso && (
            <form
              onSubmit={(e) => {
                e.preventDefault();
              }}
            >
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>Finance Families</h5>
                </div>
                <div className="ibox-content">
                  <div
                    className="table-responsive"
                    id="scroll-fix-header-table"
                  >
                    <FieldArray
                      rerenderOnEveryChange
                      name="financeFamilies"
                      isGrantedCreate={!!isGrantedCreate}
                      factories={factories}
                      sso={sso}
                      component={FinanceFamiliesArray}
                    />
                  </div>
                </div>
              </div>
            </form>
          )}
        </div>
        <div className="hidden-lg">
          <div className="text-center">
            <h2 style={{ color: "red", fontWeight: "bold" }}>
              {Translator.trans("recommendation")}
            </h2>
          </div>
        </div>
      </div>
    );
  }
}

const formConfiguration = {
  form: "finance_family_quick_edit",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
};

const mapStateToProps = (state: RootState) => {
  const connectedUser = getUserDetails(state);
  const factories = getLocationsMapping(state, "factories");
  const defaultSSO = getLocationMapping(
    { ...state, erp: connectedUser.erp },
    "ssos"
  );
  const financeFamilies = getFinanceFamiliesMapping(state);
  return {
    factories,
    initialValues: {
      financeFamilies,
      sso: defaultSSO,
    },
    financeFamilies,
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>(formConfiguration)(FinanceFamiliesQuickEdit)
);
