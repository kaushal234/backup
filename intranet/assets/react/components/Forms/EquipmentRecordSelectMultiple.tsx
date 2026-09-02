import React from "react";
import Select from "react-select";
import { connect } from "react-redux";
import {
  fetchEquipmentRecords as fetchEquipmentRecordsAction,
  fetchEquipmentRecordsByProducts as fetchEquipmentRecordsByProductsAction,
} from "../../actions/equipmentRecordsActions";
import { getEquipmentRecordsMapping } from "../../selectors/equipmentRecord/equipmentRecordSelectors";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  filterProducts: any;
  value: any;
  fetchEquipmentRecords: any;
  fetchEquipmentRecordsByProducts: any;
  equipmentRecords: any;
  equipmentRecordsListIsLoading: any;
  name: any;
}

class EquipmentRecordSelectMultiple extends React.Component<IProps> {
  products: any;

  value: any;

  constructor(props: IProps) {
    super(props);
    this.products = props.filterProducts
      ? JSON.parse(props.filterProducts)
      : [];
    this.value = props.value ? JSON.parse(props.value) : null;
    if (this.products.length) {
      this.loadDefaultOptionsByProduct();
    }
  }

  loadOptions = (inputValue: any) => {
    const { fetchEquipmentRecords } = this.props;
    if (!inputValue || inputValue.length < 3) {
      return;
    }
    fetchEquipmentRecords(inputValue);
  };

  loadDefaultOptionsByProduct() {
    const { fetchEquipmentRecordsByProducts } = this.props;
    fetchEquipmentRecordsByProducts(this.products);
  }

  render() {
    const { equipmentRecords, equipmentRecordsListIsLoading, name } =
      this.props;
    return (
      <Select
        isMulti
        options={equipmentRecords}
        isLoading={equipmentRecordsListIsLoading}
        onInputChange={this.loadOptions}
        defaultValue={this.value}
        name={name}
        placeholder={<div>Type to search an ER</div>}
      />
    );
  }
}

const mapStateToProps = (state: RootState) => {
  const { equipmentRecord } = state;
  return {
    equipmentRecords: getEquipmentRecordsMapping(state),
    equipmentRecordsListIsLoading:
      equipmentRecord.equipmentRecordsListIsLoading,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchEquipmentRecords: (search: any) => {
      dispatch(fetchEquipmentRecordsAction(search));
    },
    fetchEquipmentRecordsByProducts: (products: any) => {
      dispatch(fetchEquipmentRecordsByProductsAction(products));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(EquipmentRecordSelectMultiple);
