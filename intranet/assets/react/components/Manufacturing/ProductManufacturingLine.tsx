import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import {
  clearData as clearDataAction,
  updateProductManufacturing as updateProductManufacturingAction,
} from "../../actions/manufacturing/productManufacturingsActions";
import { productManufacturingFactory } from "../../model/form/product_manufacturing/factory";
import Loader from "../Loader";
import { AppDispatch } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  factory: any;
  productPath: any;
  product: any;
  clearData: any;
  updateProductManufacturing: any;
  index: any;
  year: any;
}

interface IState {
  modified: any;
}

class ProductManufacturingLine extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      modified: false,
    };
    this.checkEmptyFields = this.checkEmptyFields.bind(this);
  }

  checkEmptyFields(product: any, factory: any, year: any) {
    return (
      (Object.hasOwn(
        product.productManufacturings[
          `${factory.value}-${product["@id"]}-${year}`
        ],
        "industrialIncorporationParameter"
      ) &&
        !Object.hasOwn(
          product.productManufacturings[
            `${factory.value}-${product["@id"]}-${year}`
          ],
          "factoryStandardEfficiency"
        )) ||
      (!Object.hasOwn(
        product.productManufacturings[
          `${factory.value}-${product["@id"]}-${year}`
        ],
        "industrialIncorporationParameter"
      ) &&
        Object.hasOwn(
          product.productManufacturings[
            `${factory.value}-${product["@id"]}-${year}`
          ],
          "factoryStandardEfficiency"
        ))
    );
  }

  render() {
    const {
      factory,
      productPath,
      product,
      clearData,
      updateProductManufacturing,
      index,
      year,
    } = this.props;

    const { modified } = this.state;

    return (
      <tr className={modified ? "modified-background-table" : ""}>
        <td className="text-center">
          <a href={`/en/private/sales/catalogue/products/${product.id}/show`}>
            {product.name}
          </a>
        </td>
        <td>
          <GenericFormComponent
            type="Field"
            name={`${productPath}.productManufacturings[${factory.value}-${product["@id"]}-${year}].modelBaseHours`}
            onChange={() => {
              this.setState({ modified: true });
            }}
            allowFloatsOnly
          />
        </td>
        <td>
          <GenericFormComponent
            type="Field"
            name={`${productPath}.productManufacturings[${factory.value}-${product["@id"]}-${year}].industrialIncorporationParameter`}
            addon="%"
            onChange={() => {
              this.setState({ modified: true });
            }}
            allowFloatsOnly
          />
        </td>
        <td>
          <GenericFormComponent
            type="Field"
            name={`${productPath}.productManufacturings[${factory.value}-${product["@id"]}-${year}].factoryStandardEfficiency`}
            addon="%"
            onChange={() => {
              this.setState({ modified: true });
            }}
            allowFloatsOnly
          />
        </td>
        <td>
          <div className="text-center">
            <div className="row">
              <div className="col-md-6">
                <a
                  className="btn btn-warning"
                  onClick={() => {
                    clearData(product, factory.value, index, year);
                    this.setState({ modified: true });
                  }}
                >
                  {Translator.trans("button.clear")}
                </a>
              </div>
              <div className="col-md-6">
                <div className="row">
                  <div className="col-md-6">
                    <a
                      href="#"
                      className={`btn btn-${
                        modified &&
                        !this.checkEmptyFields(product, factory, year)
                          ? "info"
                          : "default disabled"
                      }`}
                      onClick={() => {
                        updateProductManufacturing(product, index, year);
                        this.setState({ modified: false });
                      }}
                    >
                      <i className="fa fa-save" />
                    </a>
                  </div>
                  {!modified && (
                    <div className="text-center col-md-6">
                      {product.showLoading && (
                        <Loader
                          childStyle={{ padding: 0, height: 0 }}
                          style={{
                            top: 0,
                            bottom: 0,
                            left: 0,
                            right: 0,
                            zIndex: 100,
                          }}
                        />
                      )}
                      {product.showSuccess && (
                        <h3>
                          <span
                            className="fa fa-check"
                            style={{ color: "#348F46", margin: "0" }}
                          />
                        </h3>
                      )}
                      {product.showError && (
                        <h3>
                          <span
                            className="fa fa-times"
                            style={{ color: "#EC4858", margin: "0" }}
                          />
                        </h3>
                      )}
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </td>
      </tr>
    );
  }
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    clearData: (product: any, factory: any, index: any, year: any) =>
      dispatch(clearDataAction(product, factory, index, year)),
    updateProductManufacturing: (product: any, index: any, year: any) => {
      const formattedProduct = productManufacturingFactory(product, year);
      dispatch(updateProductManufacturingAction(formattedProduct, index));
    },
  };
};

export default connect(
  () => ({}),
  mapDispatchToProps
)(ProductManufacturingLine);
