import React from "react";
import { FieldArray, InjectedFormProps, reduxForm } from "redux-form";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getEquipmentRecordsMapping } from "../../selectors/equipmentRecord/equipmentRecordSelectors";
import EquipmentRecordsArray from "../../components/Support/EquipmentRecordsArray";
import { RootState } from "../../store";

type IFormData = any;

type IProps = unknown;

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class OnTimeDeliveryEditForm extends React.Component<IWrappedProps> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {};
  }

  render() {
    const columnHeaders = [
      {
        columnHeader: Translator.trans("service.equipment_record.title"),
        width: "100px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.serial_number"
        ),
        width: "100px",
      },
      {
        columnHeader: Translator.trans("service.equipment_record.fields.model"),
        width: "90px",
      },
      {
        columnHeader: Translator.trans("support.equipment_record.fields.type"),
        width: "100px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.end_user"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans("support.equipment_record.fields.buyer"),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.customer"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.first_green_tag_date"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.first_estimated_green_tag_date"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.estimated_green_tag_date"
        ),
        width: "170px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.green_tag_date"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.yellow_tag_date"
        ),
        width: "170px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.date_shipped"
        ),
        width: "170px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.odp_comment"
        ),
        width: "300px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.work_order"
        ),
        width: "120px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.emission_rating"
        ),
        width: "120px",
      },
      {
        columnHeader: Translator.trans(
          "support.manual_print.fields.requestedDeliveryDate"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.factory_promised_delivery_date"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans("support.equipment_record.fields.sol"),
        width: "100px",
      },
      {
        columnHeader: Translator.trans("sales_order.line.inspection"),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.pre_delivery_inspection_date"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "support.equipment_record.fields.pre_delivery_inspection_status"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans("sales_order.line.ship_with_parts"),
        width: "80px",
      },
      {
        columnHeader: Translator.trans(
          "equipment_shipping_record.fields.incoterm"
        ),
        width: "100px",
      },
      {
        columnHeader: Translator.trans("sales_order.line.incoterm_location"),
        width: "100px",
      },
      {
        columnHeader: Translator.trans("sales_order.transaction.invoice"),
        width: "150px",
      },
      {
        columnHeader: Translator.trans(
          "sales_order.factory_order.commissioning"
        ),
        width: "150px",
      },
      {
        columnHeader: Translator.trans("support.equipment_record.fields.light"),
        width: "80px",
      },
    ];
    const gridTemplateColumns = columnHeaders
      .map((column) => column.width)
      .join(" ");
    const style = {
      gridContainer: {
        display: "grid",
        gridTemplateColumns: `${gridTemplateColumns}`,
        gridGap: "0",
        padding: "0",
      },
    };

    const columns = columnHeaders.map((column, index) => (
      <div className="tableHeader" key={index}>
        {column.columnHeader}
      </div>
    ));

    return (
      <div className="ibox float-e-margin horizontalScrollContainer">
        <form
          onSubmit={(e) => {
            e.preventDefault();
          }}
        >
          <div className="ibox-title">
            <h5>ODP edit</h5>
          </div>
          <div className="ibox-content">
            <div>
              <div className="stickyHeader" style={style.gridContainer}>
                {columns}
              </div>
              <div>
                <FieldArray
                  rerenderOnEveryChange
                  name="equipmentRecords"
                  component={EquipmentRecordsArray}
                  grid={style.gridContainer}
                />
              </div>
            </div>
          </div>
        </form>
      </div>
    );
  }
}

const formConfiguration = {
  form: "odp_line_edit_form",
  enableReinitialize: true,
  keepDirtyOnReinitialize: true,
};

const mapStateToProps = (state: RootState) => {
  return {
    initialValues: { equipmentRecords: getEquipmentRecordsMapping(state) },
  };
};

export default connect(mapStateToProps)(
  reduxForm<IFormData, IProps>(formConfiguration)(OnTimeDeliveryEditForm)
);
