import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import { connect } from "react-redux";
import AirportSelect from "./AirportSelect";
import DMSSelect from "./DMSSelect";
import CustomersSelect from "./CustomersSelect";
import PeopleAsyncSelect from "./PeopleAsyncSelect";
import CompetitorAsyncSelect from "./CompetitorAsyncSelect";
import MarketIntelligenceAsyncSelect from "./MarketIntelligenceAsyncSelect";
import CountriesSelect from "./CountriesSelect";
import ProductSelect from "./ProductSelect";
import EquipmentRecordAsyncSelect from "./EquipmentRecordAsyncSelect";
import { renderReactSelect, renderReactVerticalSelect } from "./Elements";
import ProductFamilyAsyncSelect from "./ProductFamilyAsyncSelect";
import ExtranetUserAsyncSelect from "./ExtranetUserAsyncSelect";
import BusinessPartnersSelect from "./BusinessPartnersSelect";
import NonConformityAsyncSelect from "./NonConformityAsyncSelect";
import PartItemMonologisticSelect from "./PartItemMonologisticSelect";
import { RootState } from "../../store";

type IFormData = any;

interface IProps {
  initialValue: any;
  componentAlias: any;
  name: any;
  inline: any;
  required: any;
  [key: string]: any;
}

interface IMappedProps {
  initialValueLabel: any;
}

interface IState {
  selectedValue: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

const selects: any = {
  airport: AirportSelect,
  dms: DMSSelect,
  customer: CustomersSelect,
  people: PeopleAsyncSelect,
  competitor: CompetitorAsyncSelect,
  marketIntelligence: MarketIntelligenceAsyncSelect,
  country: CountriesSelect,
  product: ProductSelect,
  equipmentRecord: EquipmentRecordAsyncSelect,
  productFamily: ProductFamilyAsyncSelect,
  extranetUser: ExtranetUserAsyncSelect,
  nonConformity: NonConformityAsyncSelect,
  businessPartner: BusinessPartnersSelect,
  item: PartItemMonologisticSelect,
};

class SymfonySelect extends React.Component<IWrappedProps, IState> {
  private hiddenInputRef = React.createRef<HTMLInputElement>();

  constructor(props: IWrappedProps, context: any) {
    super(props, context);
    this.state = {
      selectedValue: props.initialValue,
    };
  }

  render() {
    const { componentAlias, name, inline, required } = this.props;
    const { selectedValue } = this.state;
    const Component = selects[componentAlias];
    return (
      <div>
        <input
          type="hidden"
          value={selectedValue}
          name={name}
          ref={this.hiddenInputRef}
        />
        <Component
          // disabled={this.props.disabled}
          onChange={(selectedOption: any) => {
            this.setState({ selectedValue: selectedOption.value }, () => {
              this.hiddenInputRef.current?.dispatchEvent(
                new Event("change", { bubbles: true })
              );
            });
          }}
          component={inline ? renderReactSelect : renderReactVerticalSelect}
          {...this.props}
          name={name ?? `${componentAlias}-select`}
          required={required}
        />
      </div>
    );
  }
}

const formConfiguration = {
  enableReinitialize: true,
};

const mapStateToProps = (state: RootState, props: IProps & IMappedProps) => {
  let values = {};
  if (props.initialValueLabel) {
    values = {
      placeholder: props.initialValueLabel,
    };
  }
  return {
    ...values,
    form: `${props.componentAlias}-${props.name}`,
    initialValues: {
      [`${props.componentAlias}-select`]: {
        value: props.initialValue,
        label: props.initialValueLabel,
      },
    },
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>(formConfiguration)(SymfonySelect)
);
