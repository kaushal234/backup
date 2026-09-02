import React, { useEffect } from "react";
import "./TocPartsCreate.css";
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
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { IDB_DATABASE, TOC_TABS } from "../../constants/constants";
import TocPartsForm from "../../components/TocPartsForm/TocPartsForm";
import { ITocPartsFormData } from "../../@type/ITocPartsFormData";
import {
  IPostTechnicianOnCallPartsApiPayload,
  postTechnicianOnCallParts,
} from "../../api/postTechnicianOnCallParts";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
  { title: "breadcrumb.toc.details", link: "" },
  { title: "breadcrumb.toc_parts.create", link: "" },
];

function TocPartsCreate() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { tocId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const handleSubmit = async (data: ITocPartsFormData) => {
    dispatch(showMainLoader(true));
    if (data) {
      const payload: IPostTechnicianOnCallPartsApiPayload = {
        ...data,
        tocId: tocId ?? "",
      };
      if (isOnline) {
        const response = await postTechnicianOnCallParts(payload);
        if (response.status === StatusCodes.CREATED) {
          dispatch(setToastMessage("toc_parts_create.toc_parts_success"));
          navigate(`${ROUTES.toc.details}/${tocId}?tab=${TOC_TABS.parts}`);
        } else {
          toastError(dispatch, response);
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_post_toc_parts,
          payload,
        });
        dispatch(setToastMessage("toc_parts_create.offline"));
        navigate(ROUTES.toc.home);
      }
    }
    dispatch(showMainLoader(false));
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
        title: "breadcrumb.toc_parts.create",
        link: "",
      },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  useEffect(() => {
    updateBreadcrumbs();
  }, []);

  return (
    <div className="toc_parts_create__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="toc-parts-create-heading"
      >
        {t("toc_parts_create.heading")}
      </Typography>
      <Paper className="toc_parts_create__form">
        <TocPartsForm onSubmit={handleSubmit} />
      </Paper>
    </div>
  );
}

export default TocPartsCreate;
