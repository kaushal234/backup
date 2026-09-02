import React from "react";
import _ from "lodash";
import SalesForecastLine from "./SalesForecastLine";

interface IProps {
  fields: any;
}

interface IState {
  synchronized: any;
}

class SalesForecastsArray extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      synchronized: {},
    };
    this.synchronize = this.synchronize.bind(this);
  }

  synchronize(enabled: any, msfr: any) {
    const { synchronized } = this.state;
    const state = enabled
      ? {
          ...synchronized,
          [msfr]: {
            msfr,
            color: `hsla(${Math.random() * 360}, 50%, 50%, 0.9)`,
          },
        }
      : _.omit(synchronized, [msfr]);

    this.setState({ synchronized: { ...state } });
  }

  render() {
    const { fields } = this.props;

    return fields.map((salesForecast: any, index: number) => {
      const sfr = fields.get(index);
      return (
        <SalesForecastLine
          salesForecast={salesForecast}
          index={index}
          key={index}
          synchronize={this.synchronize}
          connected={_.get(
            this.state,
            `synchronized[${sfr.masterSalesForecast["@id"]}]`,
            null
          )}
        />
      );
    });
  }
}

export default SalesForecastsArray;
