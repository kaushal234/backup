import React, { useEffect, useState } from "react";
import "./TocPartsUpdate.css";
import { useTranslation } from "react-i18next";
import { Paper, Typography } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import { getTechnicianOnCallById } from "../../api/getTechnicianOnCallById";
import {
  IDB_DATABASE,
  REPLACEMENT_OPTIONS,
  TOC_TABS,
} from "../../constants/constants";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import TocPartsForm from "../../components/TocPartsForm/TocPartsForm";
import { ITocPartsFormData } from "../../@type/ITocPartsFormData";
import { ITechnicianOnCallPart } from "../../@type/IGetTechnicalOnCallsResponse";
import {
  IPutTechnicianOnCallPartsApiPayload,
  putTechnicianOnCallParts,
} from "../../api/putTechnicianOnCallParts";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
  { title: "breadcrumb.toc.details", link: "" },
  { title: "breadcrumb.toc_parts.update", link: "" },
];

function TocPartsUpdate() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { tocId, tocPartId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [data, setData] = useState<ITechnicianOnCallPart>();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const handleSubmit = async (newData: ITocPartsFormData) => {
    dispatch(showMainLoader(true));
    const payload: IPutTechnicianOnCallPartsApiPayload = {
      id: tocPartId ?? "",
      data: newData,
    };
    if (isOnline) {
      const response = await putTechnicianOnCallParts(payload);
      if (response.status === StatusCodes.OK) {
        dispatch(setToastMessage("toc_parts_update.toc_parts_success"));
        navigate(`${ROUTES.toc.details}/${tocId}?tab=${TOC_TABS.parts}`);
      } else {
        toastError(dispatch, response);
      }
    } else {
      await registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_put_toc_parts,
        payload,
      });
      dispatch(setToastMessage("toc_parts_update.offline"));
      navigate(ROUTES.toc.home);
    }
    dispatch(showMainLoader(false));
  };

  const fetchTocDetail = async () => {
    dispatch(showMainLoader(true));
    const response = await getTechnicianOnCallById({ id: tocId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      const match = response.data.parts.find(
        (part) => part.id?.toString() === tocPartId
      );
      if (match) {
        setData(match);
      } else {
        dispatch(setToastMessage("toc_parts_update.not_found"));
        navigate(ROUTES.home);
      }
    } else {
      toastError(dispatch, response);
    }
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
      {
        title: "breadcrumb.toc.details",
        link: `${ROUTES.toc.details}/${tocId}`,
        appendText: `(#${tocId ?? ""})`,
      },
      {
        title: "breadcrumb.toc_parts.update",
        link: "",
      },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  useEffect(() => {
    fetchTocDetail();
    updateBreadcrumbs();
  }, []);

  if (!data) return <div />;

  return (
    <div className="toc_parts_update__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="toc-parts-update-heading"
      >
        {t("toc_parts_update.heading")}
      </Typography>
      <Paper className="toc_parts_update__form">
        <TocPartsForm
          onSubmit={handleSubmit}
          partNumber={data.partNumber ?? ""}
          vendorPartNumber={data.vendorPartNumber ?? ""}
          description={data.description}
          quantity={data.quantity.toString()}
          comment={data.comment}
          replacement={REPLACEMENT_OPTIONS.find(
            (value) => value.id === data.replacement
          )}
        />
      </Paper>
    </div>
  );
}

export default TocPartsUpdate;
