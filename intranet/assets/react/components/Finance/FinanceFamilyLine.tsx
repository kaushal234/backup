import React from "react";
import { connect } from "react-redux";
import {
  deleteFinanceFamily as deleteFinanceFamilyAction,
  handleFinanceFamilyFactories as handleFinanceFamilyFactoriesAction,
  removePricing as removePricingAction,
  writeFinanceFamily as writeFinanceFamilyAction,
} from "../../actions/finance/financeFamiliesActions";
import Loader from "../Loader";
import { financeFamilyFactory } from "../../model/form/finance/factory";
import SweetAlert from "../../utils/SweetAlert";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  financeFamily: any;
  deleteFinanceFamily: any;
  factories: any;
  financeFamilyPath: any;
  financeFamilyIndex: any;
  fields: any;
  onCheckFactoryChange: any;
  sso: any;
  saveFinanceFamily: any;
}

interface IState {
  modified: any;
  showConfirmDeletion: any;
}

class FinanceFamilyLine extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      modified: false,
      showConfirmDeletion: false,
    };
    this.checkFactoryInFamily = this.checkFactoryInFamily.bind(this);
    this.deleteFinanceFamily = this.deleteFinanceFamily.bind(this);
  }

  checkFactoryInFamily(factory: any): boolean {
    const { financeFamily } = this.props;
    return financeFamily.factories.includes(factory.value);
  }

  deleteFinanceFamily(
    financeFamily: any,
    fields: any,
    financeFamilyIndex: any
  ) {
    const { deleteFinanceFamily } = this.props;
    this.setState({ showConfirmDeletion: false });
    if (financeFamily["@id"]) {
      deleteFinanceFamily(financeFamily.id, financeFamilyIndex);
    }
    fields.remove(financeFamilyIndex);
  }

  render() {
    const {
      factories,
      financeFamily,
      financeFamilyPath,
      financeFamilyIndex,
      fields,
      onCheckFactoryChange,
      sso,
      saveFinanceFamily,
    } = this.props;

    const { modified, showConfirmDeletion } = this.state;

    if (financeFamily.softDeleted) {
      return null;
    }

    return (
      <tr className={modified ? "modified-background-table" : ""}>
        <SweetAlert
          show={showConfirmDeletion}
          title="Are you sure?"
          type="warning"
          confirmButtonColor="#DD6B55"
          confirmButtonText="Yes, delete it!"
          text="Delete this Finance Family"
          onConfirm={() =>
            this.deleteFinanceFamily(financeFamily, fields, financeFamilyIndex)
          }
        />
        <SweetAlert
          show={financeFamily && financeFamily.showErrorCreate}
          title="Creation failed"
          type="warning"
          confirmButtonColor="#DD6B55"
          confirmButtonText="OK"
          text={financeFamily.errorMessage || ""}
          onConfirm={() => {
            financeFamily.errorMessage = false;
            financeFamily.showErrorCreate = false;
            this.deleteFinanceFamily(financeFamily, fields, financeFamilyIndex);
          }}
        />
        <th
          style={{
            background: "#FFFFFF",
            color: "#0044A0",
            borderRight: "1px solid #DCDCDC",
          }}
        >
          <GenericFormComponent
            type="Field"
            name={`${financeFamilyPath}.id`}
            hidden
          />
          <input type="hidden" name={`${financeFamilyPath}.id`} />
          <div>
            <GenericFormComponent
              type="Field"
              name={`${financeFamilyPath}.name`}
              onChange={() => {
                this.setState({ modified: true });
              }}
            />
          </div>
          <br />
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <a
              href={`/en/private/finance/finance-families/${financeFamily.id}/show`}
              style={{ padding: "7px" }}
              className="btn btn-primary"
            >
              <i className="far fa-eye" />
            </a>
            <a
              className="btn btn-danger"
              style={{ padding: "7px" }}
              type="button"
              title="Remove Line"
              onClick={() => {
                this.setState({ showConfirmDeletion: true });
              }}
            >
              <i className="fa fa fa-trash" />
            </a>
            <a
              className={`btn btn-${
                this.state.modified ? "info" : "default disabled"
              }`}
              style={{ padding: "7px" }}
              onClick={() => {
                saveFinanceFamily(financeFamily, financeFamilyIndex);
                this.setState({ modified: false });
              }}
            >
              <i className="fa fa-save" />
            </a>
          </div>
          <div className={`${modified ? "text-center" : "hidden"}`}>
            <p style={{ color: "red", marginBottom: "0" }}>NOT SAVED</p>
          </div>
          {!modified && (
            <div className="text-center">
              {financeFamily.showLoading && (
                <Loader
                  childStyle={{ padding: 0, height: 0 }}
                  style={{ top: 0, bottom: 0, left: 0, right: 0, zIndex: 100 }}
                />
              )}
              {financeFamily.showSuccess && (
                <h3>
                  <span
                    className="fa fa-check"
                    style={{ color: "#348F46", margin: "0" }}
                  />
                </h3>
              )}
              {financeFamily.showErrorUpdate && (
                <h3>
                  <span
                    className="fa fa-times"
                    style={{ color: "#EC4858", margin: "0" }}
                  />
                </h3>
              )}
            </div>
          )}
        </th>
        {factories.map((factory: any, index: number) => {
          return (
            <td key={index}>
              <GenericFormComponent
                type="Field"
                name={`${financeFamilyPath}.pricings[${sso.value}-${factory.value}].averagePrice`}
                onChange={() => {
                  this.setState({ modified: true });
                }}
                allowFloatsOnly
                placeholder=""
                disabled={!this.checkFactoryInFamily(factory)}
              />
              <GenericFormComponent
                type="Field"
                name={`${financeFamilyPath}.pricings[${sso.value}-${factory.value}].averageMargin`}
                onChange={() => {
                  this.setState({ modified: true });
                }}
                allowFloatsOnly
                placeholder=""
                disabled={!this.checkFactoryInFamily(factory)}
              />
              <div style={{ display: "flex", justifyContent: "space-between" }}>
                <GenericFormComponent
                  type="Checkbox"
                  name={`${financeFamilyPath}.pricings[${sso.value}-${factory.value}].check`}
                  onChange={(event, value) => {
                    onCheckFactoryChange(
                      financeFamily,
                      factory,
                      value,
                      financeFamilyIndex,
                      sso.value
                    );
                    this.setState({ modified: true });
                  }}
                  disabled={financeFamily.name && !financeFamily.name.length}
                />
              </div>
            </td>
          );
        })}
      </tr>
    );
  }
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    onCheckFactoryChange: (
      financeFamily: any,
      factory: any,
      value: any,
      financeFamilyIndex: any,
      sso: any
    ) =>
      dispatch(
        handleFinanceFamilyFactoriesAction(
          financeFamily,
          factory,
          value,
          financeFamilyIndex,
          sso
        )
      ),
    removePricing: (
      financeFamily: any,
      sso: any,
      factory: any,
      financeFamilyIndex: any
    ) =>
      dispatch(
        removePricingAction(financeFamily, sso, factory, financeFamilyIndex)
      ),
    saveFinanceFamily: (financeFamily: any, financeFamilyIndex: any) => {
      const formattedFinanceFamily = financeFamilyFactory(financeFamily);
      dispatch(
        writeFinanceFamilyAction(formattedFinanceFamily, financeFamilyIndex)
      );
    },
    deleteFinanceFamily: (financeFamily: any, index: any) =>
      dispatch(deleteFinanceFamilyAction(financeFamily, index)),
  };
};

export default connect(null, mapDispatchToProps)(FinanceFamilyLine);
