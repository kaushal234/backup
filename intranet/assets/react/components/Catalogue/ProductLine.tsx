import React from "react";
import { connect } from "react-redux";
import FinanceFamiliesSelect from "../Forms/FinanceFamilies";
import { updateProduct } from "../../actions/catalogue/productsActions";
import productFactory from "../../model/form/catalogue/factory";
import Loader from "../Loader";
import { renderReactInlineSelect } from "../Forms/Elements";
import { AppDispatch } from "../../store";

interface IProps {
  product: any;
  name: any;
  onFamilyChange: any;
}

function ProductLine({ product, name, onFamilyChange }: IProps) {
  return (
    <tr>
      <td>
        <a href={`/en/private/sales/catalogue/products/${product.id}/show`}>
          {product.id}
        </a>
      </td>
      <td>
        <a href={`/en/private/sales/catalogue/products/${product.id}/show`}>
          {product.name}
        </a>
      </td>
      <td>
        <a
          href={`/en/private/directory/locations/${product.erpLocation.id}/show`}
        >{`${product.erpLocation.name} - ${product.erpLocation.erp}`}</a>
      </td>
      <td>
        <button
          type="button"
          className={`btn btn-${product.hidden ? "danger" : "green"} btn-sm`}
        >
          {product.hidden ? "Hidden" : "Not Hidden"}
        </button>
      </td>
      <td>
        <a href={`/en/private/sales/catalogue/families/${product.family.id}`}>
          {product.family.name}
        </a>
      </td>
      <td>
        <a
          href={`/en/private/sales/catalogue/types/${product.family.productType.id}`}
        >
          {product.family.productType.englishName}
        </a>
      </td>
      <td>
        <div className="row">
          <div className="col-sm-9">
            <FinanceFamiliesSelect
              component={renderReactInlineSelect}
              onChange={({ value }: any) => {
                onFamilyChange(product, value);
              }}
              name={`${name}.financeFamily`}
            />
          </div>
          <div className="col-sm-3">
            <div>
              {product.showLoader && (
                <Loader
                  style={{
                    position: "absolute",
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0,
                    zIndex: 20,
                  }}
                  childStyle={{ paddingTop: "1em", paddingRight: "1em" }}
                />
              )}
              {product.showSuccess && (
                <h3>
                  <span className="fa fa-check" style={{ color: "#348F46" }} />
                </h3>
              )}
            </div>
          </div>
        </div>
      </td>
    </tr>
  );
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    onFamilyChange: (product: any, financeFamily: any) => {
      const updatedProduct = productFactory({
        ...product,
        financeFamily,
      });
      dispatch(updateProduct(updatedProduct));
    },
  };
};

export default connect(() => ({}), mapDispatchToProps)(ProductLine);
