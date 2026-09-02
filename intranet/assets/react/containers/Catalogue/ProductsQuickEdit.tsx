import React from "react";
import { connect } from "react-redux";
import { FieldArray, reduxForm, change, InjectedFormProps } from "redux-form";
import Translator from "bazinga-translator";
import ProductsArray from "../../components/Catalogue/ProductsArray";
import { getProductsMapping } from "../../selectors/catalogue/productSelector";
import { RootState } from "../../store";

type IFormData = any;

interface IProps {
  dispatch: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

const formConfiguration = {
  form: "product_quick_edit",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
};

class ProductsQuickEdit extends React.Component<IWrappedProps> {
  componentDidUpdate() {
    const {
      initialValues: { products },
      form,
      dispatch,
    } = this.props;
    dispatch(change(form, "products", products));
  }

  render() {
    return (
      <form>
        <div className="ibox float-e-margins">
          <div className="ibox-title">
            <h5>{Translator.trans("catalogue.family.products")}</h5>
          </div>
          <div className="ibox-content">
            <div className="">
              <table className="table table-responsive table-hover report-table">
                <thead>
                  <tr>
                    <th>{Translator.trans("catalogue.products.id")}</th>
                    <th>{Translator.trans("catalogue.products.name")}</th>
                    <th>{Translator.trans("catalogue.products.erp")}</th>
                    <th>{Translator.trans("catalogue.products.visibility")}</th>
                    <th>
                      {Translator.trans("catalogue.family.product_family")}
                    </th>
                    <th>{Translator.trans("catalogue.type.product_type")}</th>
                    <th>
                      {Translator.trans("catalogue.products.finance_family")}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <FieldArray
                    rerenderOnEveryChange
                    name="products"
                    component={ProductsArray}
                  />
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </form>
    );
  }
}

const mapStateToProps = (state: RootState) => {
  const products = getProductsMapping(state);
  return {
    initialValues: { products },
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>(formConfiguration)(ProductsQuickEdit)
);
