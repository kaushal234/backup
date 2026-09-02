import React from "react";
import { connect } from "react-redux";
import { Field, FieldArray, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import ProductManufacturingArray from "../../components/Manufacturing/ProductManufacturingArray";
import LocationSelect from "../../components/Forms/LocationsSelect";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../../components/Forms/Elements";
import { getProductsManufacturingMapping } from "../../selectors/catalogue/productSelector";
import { RootState } from "../../store";

type IFormData = any;

interface IProps {
  isGrantedCreate: any;
}

interface IState {
  year: any;
  factory: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class ProductManufacturingForm extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps, context: any) {
    super(props, context);
    this.state = {
      factory: null,
      year: new Date().getFullYear(),
    };
  }

  render() {
    const { isGrantedCreate } = this.props;

    const { year, factory } = this.state;

    return (
      <div>
        <div className="visible-lg">
          <div className="row">
            <div className="col-md-4">
              <div className="row">
                <div className="col-md-6">
                  <LocationSelect
                    component={renderReactVerticalSelect}
                    name="factory"
                    label={Translator.trans("fields.factory")}
                    required
                    onChange={(event: any, newFactory: any) => {
                      this.setState({ factory: newFactory });
                    }}
                    placeholder={Translator.trans("directory.select.factory")}
                    locationListName="factories"
                  />
                </div>
                <div className="col-md-6">
                  <Field
                    onChange={(event, newYear) => {
                      this.setState({ year: newYear });
                    }}
                    component={renderVerticalSelect}
                    name="type"
                    placeholder="Select a year"
                    label="Year"
                    required
                    defaultValue={year}
                  >
                    {[
                      new Date(
                        new Date().setFullYear(new Date().getFullYear() - 1)
                      ).getFullYear(),
                      new Date().getFullYear(),
                      new Date(
                        new Date().setFullYear(new Date().getFullYear() + 1)
                      ).getFullYear(),
                    ].map((type) => (
                      <option value={type} key={type}>
                        {type}
                      </option>
                    ))}
                  </Field>
                </div>
              </div>
            </div>
          </div>
          {factory && (
            <form
              onSubmit={(e) => {
                e.preventDefault();
              }}
            >
              <div className="ibox float-e-margins">
                <div className="ibox-title">
                  <h5>{Translator.trans("product_manufacturing.title")}</h5>
                </div>
                <div className="ibox-content">
                  <div className="" id="scroll-fix-header-table">
                    <table className="table matrix-table table-responsive">
                      <thead>
                        <tr>
                          <th className="top-label">
                            {Translator.trans("catalogue.products.product")}
                          </th>
                          <th className="text-center">
                            {Translator.trans(
                              "product_manufacturing.fields.mbh"
                            )}
                          </th>
                          <th className="text-center">
                            {Translator.trans(
                              "product_manufacturing.fields.iip"
                            )}
                          </th>
                          <th className="text-center">
                            {Translator.trans(
                              "product_manufacturing.fields.fse"
                            )}
                          </th>
                          <th className="text-center">
                            {Translator.trans(
                              "product_manufacturing.fields.admin"
                            )}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <FieldArray
                          name="products"
                          rerenderOnEveryChange
                          isGrantedCreate={!!isGrantedCreate}
                          year={year}
                          factory={factory}
                          component={ProductManufacturingArray as any}
                        />
                      </tbody>
                    </table>
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
  form: "product_manufacturing_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
};

const mapStateToProps = (state: RootState) => {
  return {
    initialValues: {
      products: getProductsManufacturingMapping(state),
      type: new Date().getFullYear(),
    },
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>(formConfiguration)(ProductManufacturingForm)
);
