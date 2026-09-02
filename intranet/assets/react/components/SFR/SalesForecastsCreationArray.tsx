import React from "react";
import SalesForecastCreationItem from "./SalesForecastCreationItem";

interface IProps {
  fields: any;
  onFactoryProductChange: any;
  currency: any;
}

const renderSalesForecastForm = ({
  fields,
  onFactoryProductChange,
  currency,
}: IProps) => {
  const salesForecasts = fields.getAll();
  return (
    <div>
      {salesForecasts.length < 10 && (
        <div>
          <button
            className="btn btn-info"
            type="button"
            onClick={() =>
              fields.push({
                airport:
                  (salesForecasts[0] && salesForecasts[0].airport) || null,
                estimatedSaleDate:
                  (salesForecasts[0] && salesForecasts[0].estimatedSaleDate) ||
                  null,
                customerSuccessPercentage:
                  (salesForecasts[0] &&
                    salesForecasts[0].customerSuccessPercentage) ||
                  null,
              })
            }
          >
            <i className="fa fa-fw fa-plus" />
            &nbsp;Add Sales Forecast
          </button>
        </div>
      )}
      <div className="row">
        {fields.map((salesForecast: any, index: number) => (
          <div className="col-md-6" key={index}>
            <div className="card ">
              <div className="card-header">
                {!salesForecasts[index].submitted && (
                  <button
                    className="btn btn-sm btn-danger float-end"
                    type="button"
                    title="Remove Sales Forecasts"
                    onClick={() => fields.remove(index)}
                  >
                    <i className="fa fa-fw fa-trash" />
                  </button>
                )}
                <h4>SFR #{index + 1}</h4>
              </div>
              {!salesForecasts[index].submitted && (
                <SalesForecastCreationItem
                  index={index}
                  salesForecast={salesForecast}
                  currency={currency}
                  onFactoryProductChange={onFactoryProductChange}
                />
              )}
              {salesForecasts[index].submitted && (
                <div className="text-center">
                  <h1 style={{ color: "green" }}>
                    <i className="fa fa-fw fa-check" />
                  </h1>
                  <h1>SAVED</h1>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default renderSalesForecastForm;
