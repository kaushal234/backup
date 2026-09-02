import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import moment from "moment";
import Swal from "sweetalert2";
import Translator from "bazinga-translator";
import { renderInlineSelect } from "../Forms/Elements";
import { hideErrorAlert } from "../../actions/genericActions";
import {
  onTimeDeliveryEquipmentRecordFactory,
  onTimeDeliveryPreDeliveryInspectionFactory,
} from "../../model/form/on_time_delivery_planning/factory";
import { updateEquipmentRecord as updateEquipmentRecordAction } from "../../actions/equipmentRecordsActions";
import {
  updatePreDeliveryInspectionStatus,
  writePreDeliveryInspection,
} from "../../actions/inspections/preDeliveryInspectionActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  equipmentRecord: any;
  showError?: any;
  errorMessage?: any;
  hideErrorMessage?: any;
  greenTagDate?: any;
  lastPreDeliveryInspection?: any;
  showSuccess?: any;
  equipmentRecordPath: any;
  user?: any;
  grid: any;
  index: any;
  updateEquipmentRecord?: any;
}

interface IState {
  equipmentRecordBeforeChanges: any;
}

class OnTimeDeliveryLine extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    const { equipmentRecord } = this.props;
    this.state = {
      equipmentRecordBeforeChanges: { ...equipmentRecord },
    };
  }

  componentDidUpdate(prevProps: IProps) {
    const { showError, errorMessage, hideErrorMessage } = this.props;
    if (showError && !prevProps.showError) {
      // Transform the link in the error message to a clickable link
      const formattedErrorMessage = (errorMessage || "").replace(
        /(https?:\/\/[^\s]+)/g,
        '<a href="$1" target="_blank">Click here</a>'
      );

      Swal.fire({
        icon: "warning",
        html: formattedErrorMessage,
        title: "Edition failed",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "OK",
      }).then(hideErrorMessage);
    }
  }

  render() {
    const {
      equipmentRecord,
      greenTagDate,
      lastPreDeliveryInspection,
      showSuccess,
      equipmentRecordPath,
      user,
      grid,
      index,
      updateEquipmentRecord,
    } = this.props;

    const { equipmentRecordBeforeChanges } = this.state;

    return (
      <div>
        <div style={{ display: "flex", marginRight: "0" }}>
          <div style={grid} className={showSuccess ? "updated" : ""}>
            <div className="cell">
              <p className="text-start">{equipmentRecord.legacyId}</p>
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.serialNumber}</p>
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.model}</p>
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.type}</p>
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.endUser?.name}</p>
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.buyer?.name}</p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.customerSerialNumber}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {moment(equipmentRecord.firstGreenTagDate).isValid()
                  ? moment(equipmentRecord.firstGreenTagDate)
                      .format("YYYY-MM-DDTHH:mm:ssZ")
                      .substring(0, 10)
                  : Translator.trans(
                      "support.equipment_record.info.never_green_tagged"
                    )}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {moment(equipmentRecord.firstEstimatedGreenTagDate).isValid()
                  ? moment(equipmentRecord.firstEstimatedGreenTagDate).format(
                      "YYYY-MM-DD"
                    )
                  : ""}
              </p>
            </div>
            <div className="cell">
              <GenericFormComponent
                type="DatePicker"
                name={`${equipmentRecordPath}.estimatedGreenTagDate`}
                disabled={
                  !user.currentUser.isFromSupportTeam &&
                  !user.currentUser.isMooEr &&
                  !user.currentUser.isOdpAdmin
                }
              />
            </div>
            <div className="cell">
              {!moment(greenTagDate).isValid() ? (
                <GenericFormComponent
                  type="Checkbox"
                  name={`${equipmentRecordPath}.greenTagDate`}
                  disabled={
                    !user.currentUser.isFromQualityTeam &&
                    !user.currentUser.isMooEr &&
                    !user.currentUser.isOdpAdmin
                  }
                  centered
                />
              ) : (
                <p>
                  {moment(greenTagDate)
                    .format("YYYY-MM-DDTHH:mm:ssZ")
                    .substring(0, 10)}
                </p>
              )}
            </div>
            <div className="cell">
              <GenericFormComponent
                type="DatePicker"
                name={`${equipmentRecordPath}.yellowTagDate`}
                disabled={
                  !user.currentUser.isFromQualityTeam &&
                  !user.currentUser.isMooEr &&
                  !user.currentUser.isOdpAdmin
                }
              />
            </div>
            <div className="cell">
              <GenericFormComponent
                type="DatePicker"
                name={`${equipmentRecordPath}.dateShipped`}
                disabled={
                  !user.currentUser.isFromSupportTeam &&
                  !user.currentUser.isMooEr &&
                  !user.currentUser.isOdpAdmin
                }
              />
            </div>
            <div className="cell">
              <GenericFormComponent
                type="Field"
                name={`${equipmentRecordPath}.odpComment`}
                isTextArea
                disabled={
                  !user.currentUser.isFromSupportTeam &&
                  !user.currentUser.isMooEr &&
                  !user.currentUser.isOdpAdmin
                }
                rows={2}
              />
            </div>
            <div className="cell">
              <p className="text-start">{equipmentRecord.workOrder}</p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.emissionRating?.name}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {moment(
                  equipmentRecord.orderFactory?.requestedDeliveryDate
                ).isValid()
                  ? moment(
                      equipmentRecord.orderFactory?.requestedDeliveryDate
                    ).format("YYYY-MM-DD")
                  : ""}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {moment(
                  equipmentRecord.orderFactory?.factoryPromisedDeliveryDate
                ).isValid()
                  ? moment(
                      equipmentRecord.orderFactory?.factoryPromisedDeliveryDate
                    ).format("YYYY-MM-DD")
                  : ""}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.orderLine?.legacyId}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.orderLine?.inspection}
              </p>
            </div>
            <div className="cell">
              {lastPreDeliveryInspection?.status === "SUCCESSFUL" ? (
                <p className="text-start">
                  {lastPreDeliveryInspection.plannedAt.substring(0, 10)}
                </p>
              ) : (
                <GenericFormComponent
                  type="DatePicker"
                  name={`${equipmentRecordPath}.lastPreDeliveryInspection.plannedAt`}
                  disabled={!equipmentRecord.userCanAdminPdi}
                />
              )}
            </div>
            <div className="cell">
              {lastPreDeliveryInspection?.status === "SCHEDULED" ? (
                <Field
                  name={`${equipmentRecordPath}.lastPreDeliveryInspection.status`}
                  component={renderInlineSelect}
                  disabled={!equipmentRecord.userCanAdminPdi}
                >
                  {["SCHEDULED", "FAILED", "SUCCESSFUL"].map((status) => (
                    <option value={status} key={status}>
                      {status}
                    </option>
                  ))}
                </Field>
              ) : (
                <p className="text-start">
                  {lastPreDeliveryInspection
                    ? lastPreDeliveryInspection.status
                    : ""}
                </p>
              )}
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.orderLine?.shipWithParts
                  ? Translator.trans("yes")
                  : Translator.trans("no")}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.orderLine?.incoterm.code}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.orderLine?.incotermLocation}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderTransaction?.invoice}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.orderFactory?.commissioning}
              </p>
            </div>
            <div className="cell">
              <p className="text-start">
                {equipmentRecord.light
                  ? Translator.trans("yes")
                  : Translator.trans("no")}
              </p>
            </div>
          </div>
          <div className={!showSuccess ? "cell save" : "cell hidden"}>
            <button
              className="btn btn-info mx-auto text-nowrap"
              onClick={() => {
                updateEquipmentRecord(
                  equipmentRecord,
                  index,
                  equipmentRecordBeforeChanges
                );
              }}
            >
              <i className="fa fa-fw fa-save" />
              &nbsp;Save
            </button>
          </div>
        </div>
      </div>
    );
  }
}

const mapStateToProps = (state: RootState, ownProps: IProps) => {
  return {
    ...ownProps,
    showSuccess: state.equipmentRecord.updatedLinesSuccess?.includes(
      ownProps.equipmentRecord["@id"]
    ),
    showError: state.equipmentRecord.showError,
    greenTagDate:
      state.equipmentRecord.equipmentRecords[ownProps.equipmentRecord["@id"]]
        .greenTagDate,
    lastPreDeliveryInspection:
      state.equipmentRecord.equipmentRecords[ownProps.equipmentRecord["@id"]]
        .lastPreDeliveryInspection,
    errorMessage: state.equipmentRecord.errorMessage,
    user: state.user,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    updateEquipmentRecord: (
      equipmentRecord: any,
      index: any,
      equipmentRecordBeforeChanges: any
    ) => {
      const updatedEquipmentRecord = onTimeDeliveryEquipmentRecordFactory(
        equipmentRecord,
        equipmentRecordBeforeChanges
      );
      const updatedPreDeliveryInspection =
        onTimeDeliveryPreDeliveryInspectionFactory(
          equipmentRecord.lastPreDeliveryInspection,
          equipmentRecordBeforeChanges.lastPreDeliveryInspection,
          equipmentRecordBeforeChanges["@id"]
        );

      const preDeliveryInspectionChanged =
        equipmentRecord.lastPreDeliveryInspection;
      const preDeliveryInspectionBeforeChanges =
        equipmentRecordBeforeChanges.lastPreDeliveryInspection;
      const preDeliveryInspectionStatusBeforeChanges =
        equipmentRecordBeforeChanges.lastPreDeliveryInspection.status;

      delete equipmentRecord.lastPreDeliveryInspection;
      delete equipmentRecordBeforeChanges.lastPreDeliveryInspection;

      if (
        JSON.stringify(equipmentRecord) !==
        JSON.stringify(equipmentRecordBeforeChanges)
      ) {
        dispatch(
          updateEquipmentRecordAction(
            updatedEquipmentRecord,
            `/equipment_records_odp/${equipmentRecord.id}`,
            index
          )
        );
      }
      if (preDeliveryInspectionBeforeChanges !== updatedPreDeliveryInspection) {
        dispatch(
          writePreDeliveryInspection(
            updatedPreDeliveryInspection,
            equipmentRecord["@id"],
            index
          )
        );
        if (
          preDeliveryInspectionStatusBeforeChanges !==
          updatedPreDeliveryInspection.status
        ) {
          dispatch(
            updatePreDeliveryInspectionStatus(
              updatedPreDeliveryInspection,
              index
            )
          );
        }
      }

      equipmentRecord.lastPreDeliveryInspection = preDeliveryInspectionChanged;
      equipmentRecordBeforeChanges.lastPreDeliveryInspection =
        preDeliveryInspectionBeforeChanges;
    },
    hideErrorMessage: () => dispatch(hideErrorAlert("ODP")),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(OnTimeDeliveryLine);
