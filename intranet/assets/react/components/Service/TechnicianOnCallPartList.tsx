import React, { useState } from "react";
import { Table } from "react-bootstrap";
import { useDispatch } from "react-redux";
import { change } from "redux-form";
import Swal from "sweetalert2";
import Translator from "bazinga-translator";
import moment from "moment/moment";
import Tooltip from "@mui/material/Tooltip";
import { setGlobalLoader } from "../../actions/common/loaderActions";
import { deleteTechnicianOnCallPart } from "../../api/deleteTechnicianOnCallPart";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";
import { TechnicianOnCallPartLine } from "./TechnicianOnCallPartLine";
import "./TechnicianOnCallPartList.css";

interface IProps {
  technicianOnCall: any;
  setDisplayFormPart: any;
  setEditableTocPartId: any;
  hideForm: any;
  displayFormPart: boolean;
}

interface IDefectivePart {
  id: string;
  partNumber: string;
  description: string;
  quantity: number;
}

function TechnicianOnCallPartList({
  technicianOnCall,
  setDisplayFormPart,
  setEditableTocPartId,
  hideForm,
  displayFormPart,
}: IProps) {
  const dispatch = useDispatch();
  const [hasChecked, setHasChecked] = useState(false);

  const handleCheckboxChange = () => {
    const anyChecked = Array.from(
      document.querySelectorAll<HTMLInputElement>('input[name^="create_spr"]')
    ).some((el) => el.checked);
    setHasChecked(anyChecked);
  };

  const removePart = async ({ tocPartId }: any) => {
    const confirmResponse = await Swal.fire({
      icon: "warning",
      text: Translator.trans("toc.messages.information.delete_part"),
      title: Translator.trans("toc.title.delete_part"),
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: Translator.trans("yes"),
      cancelButtonText: Translator.trans("no"),
    });

    if (confirmResponse.isConfirmed) {
      dispatch(setGlobalLoader(true));
      await deleteTechnicianOnCallPart({ tocPartId });
      await dispatch(
        fetchTechnicianOnCallThunk({ tocId: technicianOnCall.id })
      );
      dispatch(setGlobalLoader(false));
    }
  };

  const editPart = ({ tocPartId }: any) => {
    const match = technicianOnCall.parts.find(
      (part: any) => part.id === tocPartId
    );

    if (match) {
      dispatch(change("toc_parts", "partNumber", match.partNumber));
      dispatch(change("toc_parts", "vendorPartNumber", match.vendorPartNumber));
      dispatch(change("toc_parts", "serialNumber", match.serialNumber));
      dispatch(change("toc_parts", "description", match.description));
      dispatch(change("toc_parts", "quantity", match.quantity));
      dispatch(change("toc_parts", "comment", match.comment));
      dispatch(change("toc_parts", "replacement", match.replacement));
      dispatch(change("toc_parts", "defective", match.defective));
      dispatch(
        change("toc_parts", "quotationRequired", match.quotationRequired)
      );

      setDisplayFormPart(true);
      setEditableTocPartId(match.id);
    }
  };

  const createSpr = (e: any) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const formProps = Object.fromEntries(formData);
    const partsList = Object.values(formProps);

    const listUrl = partsList.reduce(
      (accumulator, currentValue) => `${accumulator}&parts[]=${currentValue}`,
      ""
    );

    window.location.href = `/en/private/parts/spare-parts-requests/toc/${technicianOnCall.id}/add-parts?equipmentRecord=${technicianOnCall.equipmentRecord["@id"]}&activity=${technicianOnCall.serviceActivity.name}&type=${technicianOnCall.technicianOnCallType.name}${listUrl}`;
  };

  return (
    <>
      {/* Defective parts list */}
      {technicianOnCall?.defectiveParts &&
        technicianOnCall.defectiveParts.length > 0 && (
          <div className="border p-2">
            <h3 className="mt-3">
              {Translator.trans("toc.title.defective_parts")}
            </h3>
            <Table hover responsive>
              <thead>
                <tr>
                  <th>PartNumber</th>
                  <th className="description-size">
                    {Translator.trans("toc.fields.parts.description")}
                  </th>
                  <th>{Translator.trans("toc.fields.parts.quantity")}</th>
                </tr>
              </thead>
              <tbody>
                {technicianOnCall.defectiveParts.map(
                  (defectivePart: IDefectivePart) => (
                    <tr key={defectivePart.id}>
                      <td>{defectivePart.partNumber}</td>
                      <td>{defectivePart.description}</td>
                      <td>{defectivePart.quantity}</td>
                    </tr>
                  )
                )}
              </tbody>
            </Table>
          </div>
        )}

      {/* SPR parts list */}
      <div className="mt-2 border border-info p-2">
        <p className="mt-2 col-9">
          {technicianOnCall &&
            (technicianOnCall.technicianOnCallType.name !==
            "toc.type.not_define_yet" ? (
              <a
                href={`/en/private/parts/spare-parts-requests/toc/${technicianOnCall.id}/add-parts?equipmentRecord=${technicianOnCall.equipmentRecord["@id"]}&activity=${technicianOnCall.serviceActivity.name}&type=${technicianOnCall.technicianOnCallType.name}`}
                className="btn btn-info col-3 me-3"
              >
                {Translator.trans("toc.button.order_spr")}
              </a>
            ) : (
              <span className="btn btn-secondary col-3 me-3 technician_on_call_part_list__order_spr_disabled">
                {Translator.trans(
                  "toc.messages.information.order_spr_disabled"
                )}
              </span>
            ))}
          {Translator.trans("toc.messages.information.delete_spr")}
        </p>
        <h3 className="mt-3" style={{ color: "#23c6c8" }}>
          {Translator.trans("toc.title.spr_parts")}
        </h3>
        <Table hover responsive>
          <thead>
            <tr>
              <th>SPR Parts</th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.description")}
              </th>
              <th>{Translator.trans("toc.fields.parts.quantity")}</th>
              <th>{Translator.trans("fields.created_at")}</th>
              <th>{Translator.trans("fields.created_by")}</th>
              <th>
                {Translator.trans("toc.fields.parts.spare_parts_request")}
              </th>
              <th>{Translator.trans("fields.status")}</th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.tracking_number")}
              </th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.comment")}
              </th>
            </tr>
          </thead>
          <tbody>
            {technicianOnCall &&
              technicianOnCall.sparePartsRequests.map(
                (sparePartsRequest: any) => {
                  return sparePartsRequest.parts.map((part: any) => {
                    return (
                      <TechnicianOnCallPartLine
                        part={part}
                        sparePartsRequestId={sparePartsRequest.id}
                        sparePartsRequestStatus={sparePartsRequest.status}
                      />
                    );
                  });
                }
              )}
            {technicianOnCall &&
              technicianOnCall.sparePartsRequests.length === 0 && (
                <tr>
                  <td colSpan={15}>No SPR found</td>
                </tr>
              )}
          </tbody>
        </Table>
      </div>

      <div className="mt-2 border border-primary p-2">
        <button className="btn btn-primary" onClick={hideForm} type="button">
          {displayFormPart
            ? Translator.trans("toc.button.parts.hide_form")
            : Translator.trans("toc.button.parts.show_form")}
        </button>
        <h3 className="mt-3 text-primary">
          {Translator.trans("toc.title.information_parts")}
        </h3>
        <form onSubmit={createSpr}>
          <Table hover responsive>
            <thead>
              <tr>
                {technicianOnCall &&
                  technicianOnCall.technicianOnCallType.name !==
                    "toc.type.not_define_yet" && (
                    <th style={{ textTransform: "capitalize" }}>
                      {Translator.trans("toc.table.create_spr")}
                    </th>
                  )}
                <th>{Translator.trans("toc.fields.parts.part_number")}</th>
                <th>
                  {Translator.trans("toc.fields.parts.vendor_part_number")}
                </th>
                <th className="description-size">
                  {Translator.trans("toc.fields.parts.description")}
                </th>
                <th>{Translator.trans("toc.fields.parts.qty")}</th>
                <th>{Translator.trans("fields.created_at")}</th>
                <th>{Translator.trans("fields.created_by")}</th>
                <th>{Translator.trans("toc.fields.parts.replacement")}</th>
                <th className="text-capitalize">
                  {Translator.trans("menu.action")}
                </th>
              </tr>
            </thead>
            <tbody>
              {technicianOnCall &&
                technicianOnCall.parts.map((part: any) => {
                  return (
                    <tr key={part.id}>
                      {technicianOnCall.technicianOnCallType.name !==
                        "toc.type.not_define_yet" && (
                        <td>
                          <input
                            type="checkbox"
                            name={`create_spr[_${part.id}]`}
                            value={`${part["@id"]}`}
                            onChange={handleCheckboxChange}
                          />
                        </td>
                      )}
                      <td>{part.partNumber}</td>
                      <td>{part.vendorPartNumber}</td>
                      <td style={{ maxWidth: "200px" }}>
                        <Tooltip
                          title={
                            <span
                              style={{ whiteSpace: "pre-wrap" }}
                              // eslint-disable-next-line react/no-danger
                              dangerouslySetInnerHTML={{
                                __html: part.description ?? "",
                              }}
                            />
                          }
                          placement="right"
                          slotProps={{
                            popper: { disablePortal: true },
                            tooltip: {
                              sx: {
                                maxWidth: 400,
                              },
                            },
                          }}
                        >
                          <span
                            className="text-truncate d-block"
                            // eslint-disable-next-line react/no-danger
                            dangerouslySetInnerHTML={{
                              __html: part.description ?? "",
                            }}
                          />
                        </Tooltip>
                      </td>
                      <td>{part.quantity}</td>
                      <td>{moment(part.createdAt).format("YYYY-MM-DD")}</td>
                      <td>
                        {part.createdBy.firstname} {part.createdBy.lastname}
                      </td>
                      <td>{part.replacement}</td>
                      <td className="text-nowrap">
                        <div className="d-flex gap-1">
                          <span
                            className="d-inline-block btn btn-primary"
                            title={
                              part.comment ||
                              Translator.trans("comments.no_comment")
                            }
                          >
                            <i className="fa-solid fa-comment" />
                          </span>
                          <button
                            type="button"
                            className="d-inline-block btn btn-primary"
                            onClick={() => editPart({ tocPartId: part.id })}
                          >
                            <i className="fa fa-edit" />
                          </button>
                          <button
                            type="button"
                            className="d-inline-block btn btn-danger"
                            onClick={() => removePart({ tocPartId: part.id })}
                          >
                            <i className="fa fa-trash" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}

              {technicianOnCall && technicianOnCall.parts.length === 0 && (
                <tr>
                  <td colSpan={15}>No parts found</td>
                </tr>
              )}
            </tbody>
          </Table>

          {technicianOnCall &&
            hasChecked &&
            technicianOnCall.technicianOnCallType.name !==
              "toc.type.not_define_yet" && (
              <div className="text-end">
                <button className="btn btn-info" type="submit">
                  {Translator.trans("toc.button.parts.create_spr")}
                </button>
              </div>
            )}
        </form>
      </div>

      {/* SPR parts deleted list */}
      <div className="mt-2 border border-danger p-2">
        <h3 className="mt-3 text-danger">
          {Translator.trans("toc.title.spr_deleted_parts")}
        </h3>
        <Table hover responsive>
          <thead>
            <tr>
              <th>SPR Parts</th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.description")}
              </th>
              <th>{Translator.trans("toc.fields.parts.quantity")}</th>
              <th>{Translator.trans("fields.created_at")}</th>
              <th>{Translator.trans("fields.created_by")}</th>
              <th>
                {Translator.trans("toc.fields.parts.spare_parts_request")}
              </th>
              <th>{Translator.trans("fields.status")}</th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.tracking_number")}
              </th>
              <th className="description-size">
                {Translator.trans("toc.fields.parts.comment")}
              </th>
            </tr>
          </thead>
          <tbody>
            {technicianOnCall &&
              technicianOnCall.sparePartsRequests.map(
                (sparePartsRequest: any) => {
                  return sparePartsRequest.deletedParts.map((part: any) => {
                    return (
                      <TechnicianOnCallPartLine
                        part={part}
                        sparePartsRequestId={sparePartsRequest.id}
                        sparePartsRequestStatus={sparePartsRequest.status}
                      />
                    );
                  });
                }
              )}

            {technicianOnCall &&
              technicianOnCall.sparePartsRequests.length === 0 && (
                <tr>
                  <td colSpan={15}>No SPR deletd found</td>
                </tr>
              )}
          </tbody>
        </Table>
      </div>
    </>
  );
}

export { TechnicianOnCallPartList };
