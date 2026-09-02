import React, { useEffect, useState } from "react";
import "./TocCreate.css";
import { useTranslation } from "react-i18next";
import { Paper, Typography } from "@mui/material";
import { useLocation, useNavigate } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import TocForm from "../../components/TocForm/TocForm";
import { ITocFormData } from "../../@type/ITocFormData";
import {
  IPostTechnicianOnCallApiPayload,
  postTechnicianOnCall,
} from "../../api/postTechnicianOnCall";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import {
  setToastMessage,
  setToastMessageWithParams,
} from "../../redux/slices/toastSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import TocFileForm from "../../components/TocFileForm/TocFileForm";
import { ITocFileFormData } from "../../@type/ITocFileFormData";
import { createPeopleDropdownItem } from "../../utils/dropdown/people";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import {
  IDB_DATABASE,
  TOC_CREATE_PARAMS,
  TOC_FILTER_IFACTOR_OPTIONS,
} from "../../constants/constants";
import { createTechnicianOnCallTypeDropdownItem } from "../../utils/dropdown/technicianOnCallType";
import { getFormattedTodayDate } from "../../utils/date";
import {
  IPostTechnicianOnCallFilesApiPayload,
  postTechnicianOnCallFiles,
} from "../../api/postTechnicianOnCallFiles";
import { IPostTechnicianOnCallFiles } from "../../@type/IPostTechnicianOnCallFiles";
import { IPostTechnicianOnCallAndFilesApiPayload } from "../../api/postTechnicianOnCallAndFiles";
import { getAllEquipmentRecord } from "../../api/getAllEquipmentRecord";
import { createEquipmentRecordSerialNoDropdownItem } from "../../utils/dropdown/equipmentRecordSerialNumber";
import { IEquipmentRecord } from "../../@type/IGetAllEquipmentRecordResponse";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "drawer.toc.home", link: ROUTES.toc.home },
  { title: "drawer.toc.create", link: "" },
];

function TocCreate() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const userInfo = useAppSelector((state) => state.auth.userInfo);
  const [tocData, setTocData] =
    useState<IPostTechnicianOnCallApiPayload | null>(null);
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);
  const [showSecondForm, setShowSecondForm] = useState(false);
  const [tocId, setTocId] = useState("");
  const [erData, setErData] = useState<IEquipmentRecord | null>();

  const handleTocFormSubmit = async (data: ITocFormData) => {
    dispatch(showMainLoader(true));
    const params: IPostTechnicianOnCallApiPayload = {
      originalTitle: data.originalTitle,
      originalDescription: data.originalDescription,
      equipmentRecord: data.equipmentRecordSerialNo?.id ?? null,
      assignee: data.assignee?.id ?? "",
      technician: data.technician?.id,
      airport: data.airport?.id ?? "",
      errorCodes: data.errorCodes,
      unitOperationalStatus: data.unitOperationalStatus?.id ?? "",
      technicianOnCallType: data.payer?.id ?? "",
      serviceActivity: data.serviceActivity?.id ?? "",
      indiceFactor: data.ifactor?.id ?? "",
      salesOrganisationService: data.serviceOrganisation?.id ?? "",
      tags: data.tags.map((tag) => tag.id),
      mainContact: data.mainContact?.id,
      contacts: data.contacts.map((contact) => contact.id),
      hourMeter: +data.hourMeter,
      customer: data.customer?.id ?? "",
      thirdPartyName: data.thirdPartyName || null,
      thirdPartyRef: data.thirdPartyRef || null,
      serialNumber: data.serialNumber ?? "",
    };
    if (data.confidential) {
      params.confidential = data.confidential;
      params.reason = data.reason;
    }
    if (data.isTechnicianRequested) {
      params.nestedCustomerServiceRecord = {
        leader: data.serviceTechnician?.id ?? null,
        plannedAt: data.plannedDate ?? null,
      };
    }
    if (isOnline) {
      const response = await postTechnicianOnCall(params);

      if (
        response.status === StatusCodes.CREATED ||
        response.status === StatusCodes.PARTIAL_CONTENT
      ) {
        const newTocId = response.data?.id?.toString() ?? "";
        setTocId(newTocId);
        dispatch(
          setToastMessageWithParams({
            toastMessage: "toc_create.toc_success",
            params: { tocId: newTocId },
          })
        );

        const csr = response.data?.["@sub_resources"]?.customerServiceRecord;
        const csrId = csr?.id;

        if (csrId) {
          setTimeout(() => {
            dispatch(
              setToastMessageWithParams({
                toastMessage: "toc_create.csr_success",
                params: { csrId },
              })
            );
          }, 100);
        } else if (csr?.detail) {
          setTimeout(() => {
            dispatch(setToastMessage(csr.detail ?? ""));
          }, 100);
        }
        setShowSecondForm(true);
      } else {
        toastError(dispatch, response);
      }
    } else {
      setTocData(params);
      setShowSecondForm(true);
    }
    dispatch(showMainLoader(false));
  };

  const handleTocFileFormSubmit = async (data: ITocFileFormData) => {
    dispatch(showMainLoader(true));
    const files: IPostTechnicianOnCallFiles = {};
    if (data.mainFile?.length) {
      files.mainFile = {
        description: data.mainFile[0].description,
        file: data.mainFile[0].file,
      };
    }
    if (data.files?.length) {
      files.files = data.files.map((file) => ({
        description: file.description,
        file: file.file,
      }));
    }
    if (tocData) {
      const params: IPostTechnicianOnCallAndFilesApiPayload = {
        tocData,
        ...files,
      };
      await registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_post_toc_and_files,
        payload: params,
      });
      dispatch(setToastMessage("toc_create.offline"));
      navigate(ROUTES.toc.home);
    } else {
      const params: IPostTechnicianOnCallFilesApiPayload = {
        tocId,
        ...files,
      };
      if (isOnline) {
        const response = await postTechnicianOnCallFiles(params);
        if (response.status === StatusCodes.CREATED) {
          dispatch(
            setToastMessageWithParams({
              toastMessage: "toc_create.files.success",
              params: { tocId },
            })
          );
        } else {
          toastError(dispatch, response);
        }
        navigate(`${ROUTES.toc.details}/${tocId}`);
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_post_toc_files,
          payload: params,
        });
        dispatch(setToastMessage("toc_create.offline"));
        navigate(ROUTES.toc.home);
      }
    }
    dispatch(showMainLoader(false));
  };

  const peopleDropdownItem = userInfo
    ? createPeopleDropdownItem(userInfo)
    : null;

  const fetchErData = async () => {
    const serialNumber = queryParams.get(TOC_CREATE_PARAMS.erSerialNumber);
    dispatch(showMainLoader(true));
    const erPromise = getAllEquipmentRecord({
      serialNumber: serialNumber ?? "",
    });
    const [erResponse] = await Promise.all([erPromise]);
    dispatch(showMainLoader(false));
    if (erResponse.data?.["hydra:member"].length) {
      setErData(erResponse.data["hydra:member"][0]);
    } else {
      setErData(null);
    }
  };

  useEffect(() => {
    fetchErData();
  }, []);

  if (erData === undefined) return null;

  return (
    <div className="toc_create__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="toc-create-heading"
      >
        {t("toc_create.heading")}
      </Typography>
      <Paper className="toc_create__form">
        {!showSecondForm && (
          <TocForm
            onSubmit={handleTocFormSubmit}
            assignee={peopleDropdownItem}
            ifactor={TOC_FILTER_IFACTOR_OPTIONS[1]}
            payer={createTechnicianOnCallTypeDropdownItem({
              "@id": "/service/technician_on_call_types/1",
              name: "Not Defined Yet",
            })}
            serviceTechnician={peopleDropdownItem}
            plannedDate={getFormattedTodayDate()}
            equipmentRecord={
              erData
                ? createEquipmentRecordSerialNoDropdownItem({
                    ...erData,
                    equipmentRecord: erData,
                  })
                : undefined
            }
          />
        )}
        {showSecondForm && <TocFileForm onSubmit={handleTocFileFormSubmit} />}
      </Paper>
    </div>
  );
}

export default TocCreate;
