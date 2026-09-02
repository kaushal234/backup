import React from "react";
import { Field, formValueSelector } from "redux-form";
import { connect } from "react-redux";
import _ from "lodash";
import SFRComments from "./SFRComments";
import { renderInlineSelect } from "../Forms/Elements";
import { RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  salesForecast: any;
  detail?: any;
  index: any;
  color?: any;
  disabled?: any;
  synchronize: any;
}

interface IMappedProps {
  connected: any;
}

function SalesForecastLine(props: IProps) {
  const { salesForecast, detail, index, color, disabled, synchronize } = props;
  const textColor = color ? "#FFF" : "inherit";
  return (
    <tr style={{ backgroundColor: color }}>
      <td style={{ color: textColor }}>
        {detail.id}
        <br />
      </td>
      <td>
        <Field
          name={`${salesForecast}.status`}
          component={renderInlineSelect}
          disabled={disabled}
        >
          {["BUDGET", "DELAYED", "IN_PROGRESS", "CANCELLED"].map((status) => (
            <option value={status} key={status}>
              {status}
            </option>
          ))}
        </Field>
        <GenericFormComponent
          type="Field"
          name={`${salesForecast}.comment`}
          label="Comment"
          disabled={disabled}
          isTextArea
        />
      </td>
      <td style={{ color: textColor }}>
        <p>{_.get(detail, "buyer.name")}</p>
        <p>{_.get(detail, "endUser.name")}</p>
      </td>
      <td style={{ color: textColor }}>
        {_.get(detail, "buyer.country.name")}{" "}
        {_.get(detail, "airport.code")
          ? `(${_.get(detail, "airport.code")})`
          : ""}
      </td>
      <td style={{ color: textColor }}>
        <p>{_.get(detail, "factory.name")}</p>
        <p>{_.get(detail, "product.name")}</p>
      </td>
      <td>
        <GenericFormComponent
          type="DatePicker"
          name={`${salesForecast}.estimatedSaleDate`}
          className="form-control"
          dateformat="Y-M"
          views={["year", "decade"]}
          disabled={disabled}
        />
      </td>
      <td>
        <GenericFormComponent
          type="Field"
          name={`${salesForecast}.quantity`}
          id={`${salesForecast}.quantity`}
          allowFloatsOnly
          disabled={disabled}
        />
      </td>
      <td>
        <Field
          name={`${salesForecast}.customerSuccessPercentage`}
          id={`${salesForecast}.customerSuccessPercentage`}
          type="number"
          component={renderInlineSelect}
          disabled={disabled}
          style={{ minWidth: "50px" }}
        >
          {_.range(0, 105, 5).map((customerSuccessPercentage) => (
            <option
              value={customerSuccessPercentage}
              key={customerSuccessPercentage}
            >
              {customerSuccessPercentage}
            </option>
          ))}
        </Field>
      </td>
      <td>
        <Field
          name={`${salesForecast}.successPercentage`}
          id={`${salesForecast}.successPercentage`}
          type="number"
          component={renderInlineSelect}
          disabled={disabled}
          style={{ minWidth: "50px" }}
        >
          {_.range(0, 105, 5).map((successPercentage) => (
            <option value={successPercentage} key={successPercentage}>
              {successPercentage}
            </option>
          ))}
        </Field>
      </td>
      <td>
        <GenericFormComponent
          type="Checkbox"
          name={`${salesForecast}.synchronized`}
          onChange={(event: any) =>
            synchronize(event.target.checked, detail.masterSalesForecast["@id"])
          }
          disabled={disabled}
        />
      </td>
      <td>
        <GenericFormComponent
          type="Checkbox"
          name={`${salesForecast}.notificationRestricted`}
        />
      </td>
      <td align="right">
        <SFRComments index={index} />
      </td>
    </tr>
  );
}

const selector = formValueSelector("sfr_quick_edit");

const mapStateToProps = (state: RootState, props: IProps & IMappedProps) => {
  const index = !props.index ? 0 : props.index;
  const detail = selector(state, `salesForecasts[${index}]`);
  const { masterSalesForecast, synchronized } = detail;
  const color =
    masterSalesForecast &&
    props.connected &&
    props.connected.msfr === masterSalesForecast["@id"]
      ? props.connected.color
      : null;
  return {
    detail,
    index,
    color,
    disabled: color && !synchronized,
  };
};

export default connect(mapStateToProps)(SalesForecastLine);
