import React from "react";
import { Field, formValueSelector } from "redux-form";
import { connect } from "react-redux";
import _ from "lodash";
import {
  renderReactVerticalSelect,
  renderVerticalSelect,
} from "../Forms/Elements";
import LocationSelect from "../Forms/LocationsSelect";
import ProductSelect from "../Forms/ProductSelect";
import EmissionRatingsSelect from "../Forms/EmissionRatingsSelect";
import AirportSelect from "../Forms/AirportSelect";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  onFactoryProductChange: any;
  getValorization: any;
  index: any;
  salesForecast: any;
  currency: any;
}

interface IState {
  averagePrice: any;
  averageMargin: any;
  quantity: any;
}

class SalesForecastCreationItem extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      averagePrice: null,
      averageMargin: null,
      quantity: null,
    };
  }

  render() {
    const {
      onFactoryProductChange,
      getValorization,
      index,
      salesForecast,
      currency,
    } = this.props;

    const { averagePrice, averageMargin, quantity } = this.state;

    const valorization = getValorization(index);
    return (
      <div className="card-body">
        <GenericFormComponent
          type="Field"
          name={`${salesForecast}.submitted`}
          hidden
        />
        <AirportSelect
          name={`${salesForecast}.airport`}
          label="Airport"
          required={false}
        />
        <GenericFormComponent
          type="DatePicker"
          name={`${salesForecast}.estimatedSaleDate`}
          required
          label="Estimated Sale Date"
          placeholder="Select a date"
          dateformat="Y-M"
          views={["year", "decade"]}
        />
        <Field
          name={`${salesForecast}.successPercentage`}
          required
          label="Alvest Success Percentage"
          type="number"
          component={renderVerticalSelect}
          style={{ minWidth: "50px" }}
        >
          {_.range(0, 105, 5).map((successPercentage) => (
            <option
              value={successPercentage}
              key={successPercentage}
            >{`${successPercentage} %`}</option>
          ))}
        </Field>
        <Field
          name={`${salesForecast}.customerSuccessPercentage`}
          required
          label="Customer Purchase Percentage"
          type="number"
          component={renderVerticalSelect}
          style={{ minWidth: "50px" }}
        >
          {_.range(0, 105, 5).map((customerSuccessPercentage) => (
            <option
              value={customerSuccessPercentage}
              key={customerSuccessPercentage}
            >{`${customerSuccessPercentage} %`}</option>
          ))}
        </Field>
        <LocationSelect
          label="Factory"
          component={renderReactVerticalSelect}
          name={`${salesForecast}.factory`}
          onChange={onFactoryProductChange(index, "factory")}
          placeholder="Select a Factory"
          locationListName="factories"
        />
        <ProductSelect
          component={renderReactVerticalSelect}
          name={`${salesForecast}.product`}
          onChange={onFactoryProductChange(index, "product")}
          required
        />
        <EmissionRatingsSelect
          component={renderReactVerticalSelect}
          name={`${salesForecast}.tier`}
          required
        />
        <GenericFormComponent
          type="Field"
          name={`${salesForecast}.quantity`}
          required
          label="Quantity"
          onChange={(field, value) => {
            this.setState({ quantity: Number.parseInt(value, 10) });
          }}
          allowFloatsOnly
        />
        <GenericFormComponent
          type="Field"
          name={`${salesForecast}.comment`}
          required
          label="Comment"
          isTextArea
        />
        <div className="row">
          <div className="col-sm-6">
            <GenericFormComponent
              type="Field"
              name={`${salesForecast}.price`}
              onChange={(field, value) => {
                this.setState({ averagePrice: Number.parseInt(value, 10) });
              }}
              required
              label="Unit Price"
              addon={currency}
              allowFloatsOnly
            />
          </div>
          <div className="col-sm-6">
            <GenericFormComponent
              type="Field"
              name={`${salesForecast}.margin`}
              onChange={(field, value) => {
                this.setState({ averageMargin: Number.parseFloat(value) });
              }}
              required
              label="Sales Margin"
              addon="%"
              allowFloatsOnly
            />
          </div>
        </div>
        {currency !== "" &&
          (averagePrice || valorization) &&
          (averageMargin || valorization) &&
          quantity && (
            <div className="row">
              <div className="col-sm-12">
                <div className="ibox-content">
                  <p className="text-center">
                    Total Expected Sales :{" "}
                    <b>
                      {Intl.NumberFormat([], {
                        style: "currency",
                        currency,
                        minimumFractionDigits: 0,
                      }).format(
                        (averagePrice || valorization.averagePrice) * quantity
                      )}
                    </b>
                  </p>
                  <p className="text-center">
                    Total Expected Sales Margin :{" "}
                    <b>
                      {Intl.NumberFormat([], {
                        style: "currency",
                        currency,
                        maximumFractionDigits: 2,
                      }).format(
                        ((averagePrice || valorization.averagePrice) / 100) *
                          (averageMargin || valorization.averageMargin) *
                          quantity
                      )}
                    </b>
                  </p>
                </div>
              </div>
            </div>
          )}
      </div>
    );
  }
}

const selector = formValueSelector("sfr_create_form");

const mapStateToProps = (state: RootState) => {
  return {
    getValorization: (index: any) => {
      const sso = selector(state, "sso");
      const factory = selector(state, `salesForecasts[${index}].factory`);
      const product = selector(state, `salesForecasts[${index}].product`);

      if (!sso || !factory || !product || !product.financeFamily) {
        return undefined;
      }
      return state.sfr.sfrValorization[
        `${sso.value}-${factory.value}-${product.financeFamily.value}`
      ];
    },
  };
};

export default connect(mapStateToProps)(SalesForecastCreationItem);
