import React from "react";
import { IconButton, Avatar } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import CheckIcon from "@mui/icons-material/Check";
import ClearIcon from "@mui/icons-material/Clear";
import CreateIcon from "@mui/icons-material/Create";
import DeleteIcon from "@mui/icons-material/Delete";
import { StatusCodes } from "http-status-codes";
import {
  ITechnicianOnCall,
  ITechnicianOnCallPart,
} from "../../@type/IGetTechnicalOnCallsResponse";
import { ROUTES } from "../../constants/routes";
import { useConfirmation } from "../../hooks/useConfirmation";
import {
  deleteTechnicianOnCallParts,
  IDeleteTechnicianOnCallPartsApiPayload,
} from "../../api/deleteTechnicianOnCallParts";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { IDB_DATABASE, TOC_PARTS_REPLACEMENT } from "../../constants/constants";
import InfoCard from "../InfoCard/InfoCard";
import "./TocDetailPartsCards.css";

interface IProps {
  data: ITechnicianOnCall;
}

export default function TocDetailPartsCards(props: IProps) {
  const { data } = props;
  const { tocId } = useParams();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { confirmation } = useConfirmation();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const handleEdit = (parts: ITechnicianOnCallPart) => {
    navigate(
      `${ROUTES.toc.details}/${tocId}/${ROUTES.toc.parts.update}/${parts.id}`
    );
  };

  const handleDelete = async (parts: ITechnicianOnCallPart) => {
    const confirmationResponse = await confirmation(
      "toc_parts.delete.confirmation.title",
      "toc_parts.delete.confirmation.description"
    );
    if (confirmationResponse) {
      dispatch(showMainLoader(true));
      if (data) {
        const payload: IDeleteTechnicianOnCallPartsApiPayload = {
          id: parts.id?.toString() ?? "",
        };
        if (isOnline) {
          const response = await deleteTechnicianOnCallParts(payload);
          if (response.status === StatusCodes.NO_CONTENT) {
            dispatch(setToastMessage("toc_parts.delete.success"));
            dispatch(refreshTocData());
          } else {
            toastError(dispatch, response);
          }
        } else {
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_delete_toc_parts,
            payload,
          });
          dispatch(setToastMessage("toc_parts_create.offline"));
          navigate(ROUTES.toc.home);
        }
      }
      dispatch(showMainLoader(false));
    }
  };

  return (
    <div className="toc_detail_parts_cards__wrapper">
      {data.parts.map((part, idx) => (
        <InfoCard
          data={[
            {
              title: "toc_parts.table.part_number",
              value: part.partNumber || "---",
            },
            {
              title: "toc_parts.table.vendor_part_number",
              value: part.vendorPartNumber || "---",
            },
            {
              title: "toc_parts.table.description",
              value: part.description || "---",
            },
            {
              title: "toc_parts.table.quantity",
              value: part.quantity,
            },
            {
              title: "toc_parts.table.created_at",
              value: part.createdAt.slice(0, 10),
            },
            {
              title: "toc_parts.table.created_by",
              value: `${part.createdBy.firstname} ${part.createdBy.lastname}`,
            },
            {
              title: "toc_parts.table.defective",
              value: part.defective ? (
                <CheckIcon className="cui_success" />
              ) : (
                <ClearIcon className="cui_error" />
              ),
            },
            {
              title: "toc_parts.table.supplier_replaces",
              value:
                part.replacement === TOC_PARTS_REPLACEMENT.supplier ? (
                  <CheckIcon className="cui_success" />
                ) : (
                  <ClearIcon className="cui_error" />
                ),
            },
            {
              title: "toc_parts.table.customer_replaces",
              value:
                part.replacement === TOC_PARTS_REPLACEMENT.customer ? (
                  <CheckIcon className="cui_success" />
                ) : (
                  <ClearIcon className="cui_error" />
                ),
            },
            {
              title: "toc_parts.table.quotation_required",
              value:
                part.replacement === TOC_PARTS_REPLACEMENT.quotation ? (
                  <CheckIcon className="cui_success" />
                ) : (
                  <ClearIcon className="cui_error" />
                ),
            },
            {
              title: "toc_parts.table.comment",
              value: part.comment || "---",
            },
          ]}
          footer={
            <div className="toc_detail_parts_cards__actions">
              <Avatar className="cui_action">
                <IconButton
                  onClick={() => handleEdit(part)}
                  data-cy={`toc-parts-table-edit-${idx}`}
                >
                  <CreateIcon />
                </IconButton>
              </Avatar>
              <Avatar className="cui_action">
                <IconButton
                  onClick={() => handleDelete(part)}
                  data-cy={`toc-parts-table-delete-${idx}`}
                >
                  <DeleteIcon />
                </IconButton>
              </Avatar>
            </div>
          }
          dataCy={`toc-parts-${idx}`}
        />
      ))}
    </div>
  );
}
