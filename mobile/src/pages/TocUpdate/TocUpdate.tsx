import React, { useEffect } from "react";
import "./TocUpdate.css";
import { useTranslation } from "react-i18next";
import { Avatar, IconButton, Paper, Typography } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import { StatusCodes } from "http-status-codes";
import DeleteIcon from "@mui/icons-material/Delete";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import TocForm from "../../components/TocForm/TocForm";
import { ITocFormData } from "../../@type/ITocFormData";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import {
  setToastMessage,
  setToastMessageWithParams,
} from "../../redux/slices/toastSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import { getTechnicianOnCallById } from "../../api/getTechnicianOnCallById";
import { setTocDetail } from "../../redux/slices/tocDetailSlice";
import {
  IPutTechnicianOnCallApiPayload,
  putTechnicianOnCall,
} from "../../api/putTechnicianOnCall";
import { createEquipmentRecordSerialNoDropdownItem } from "../../utils/dropdown/equipmentRecordSerialNumber";
import { createAirportDropdownItem } from "../../utils/dropdown/airport";
import { createSalesOrganisationDropdownItem } from "../../utils/dropdown/salesOrganisation";
import { createPeopleDropdownItem } from "../../utils/dropdown/people";
import {
  IDB_DATABASE,
  TOC_FILTER_IFACTOR_OPTIONS,
} from "../../constants/constants";
import { createTechnicianOnCallTypeDropdownItem } from "../../utils/dropdown/technicianOnCallType";
import { createServiceActivityDropdownItem } from "../../utils/dropdown/serviceActivity";
import { createUnitOperationalStatusDropdownItem } from "../../utils/dropdown/unitOperationalStatus";
import { createTechnicianOnCallTagDropdownItem } from "../../utils/dropdown/technicianOnCallTag";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { createContactDropdownItem } from "../../utils/dropdown/contact";
import { createCustomerDropdownItem } from "../../utils/dropdown/customer";
import {
  deleteTechnicianOnCall,
  IDeleteTechnicianOnCallApiPayload,
} from "../../api/deleteTechnicianOnCall";
import { useConfirmation } from "../../hooks/useConfirmation";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "drawer.toc.home", link: ROUTES.toc.home },
  { title: "drawer.toc.details", link: "" },
  { title: "drawer.toc.update", link: "" },
];

function TocUpdate() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { tocId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { confirmation } = useConfirmation();
  const data = useAppSelector((state) => state.tocDetail.data);
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const handleDelete = async () => {
    const confirmationResponse = await confirmation(
      "toc_update.delete.confirmation.title",
      "toc_update.delete.confirmation.description"
    );
    if (!confirmationResponse) return;
    dispatch(showMainLoader(true));
    const params: IDeleteTechnicianOnCallApiPayload = {
      id: tocId ?? "",
    };
    if (isOnline) {
      const response = await deleteTechnicianOnCall(params);
      if (response.status === StatusCodes.NO_CONTENT) {
        dispatch(setToastMessage("toc_update.delete.success"));
        navigate(ROUTES.toc.home);
      } else {
        toastError(dispatch, response);
      }
    } else {
      await registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_delete_toc,
        payload: params,
      });
      dispatch(setToastMessage("toc_update.offline"));
      navigate(ROUTES.toc.home);
    }
    dispatch(showMainLoader(false));
  };

  const handleTocFormSubmit = async (newData: ITocFormData) => {
    dispatch(showMainLoader(true));
    const params: IPutTechnicianOnCallApiPayload = {
      id: tocId ?? "",
      data: {
        originalTitle: newData.originalTitle,
        originalDescription: newData.originalDescription,
        equipmentRecord: newData.equipmentRecordSerialNo?.id ?? null,
        assignee: newData.assignee?.id ?? "",
        technician: newData.technician?.id,
        airport: newData.airport?.id ?? "",
        errorCodes: newData.errorCodes,
        unitOperationalStatus: newData.unitOperationalStatus?.id ?? "",
        technicianOnCallType: newData.payer?.id ?? "",
        serviceActivity: newData.serviceActivity?.id ?? "",
        indiceFactor: newData.ifactor?.id ?? "",
        salesOrganisationService: newData.serviceOrganisation?.id ?? "",
        tags: newData.tags.map((tag) => tag.id),
        mainContact: newData.mainContact?.id,
        contacts: newData.contacts.map((contact) => contact.id),
        hourMeter: newData.hourMeter,
        customer: newData.customer?.id ?? "",
        thirdPartyName: newData.thirdPartyName || null,
        thirdPartyRef: newData.thirdPartyRef || null,
        serialNumber: newData.serialNumber ?? "",
        reason: newData.reason,
      },
    };
    if (newData.confidential !== undefined) {
      params.data.confidential = newData.confidential;
    }
    if (isOnline) {
      const response = await putTechnicianOnCall(params);
      if (response.status === StatusCodes.OK && response.data) {
        dispatch(
          setToastMessageWithParams({
            toastMessage: "toc_update.success",
            params: { tocId: `${response.data.id}` },
          })
        );
        navigate(`${ROUTES.toc.details}/${tocId}`);
      } else {
        toastError(dispatch, response);
      }
    } else {
      await registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_put_toc,
        payload: params,
      });
      dispatch(setToastMessage("toc_update.offline"));
      navigate(ROUTES.toc.home);
    }
    dispatch(showMainLoader(false));
  };

  const fetchTocDetail = async () => {
    dispatch(showMainLoader(true));
    const response = await getTechnicianOnCallById({ id: tocId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      dispatch(setTocDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "drawer.toc.home", link: ROUTES.toc.home },
      {
        title: "drawer.toc.details",
        link: `${ROUTES.toc.details}/${tocId}`,
        appendText: `(#${tocId ?? ""})`,
      },
      { title: "drawer.toc.update", link: "" },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  useEffect(() => {
    fetchTocDetail();
    updateBreadcrumbs();
    return () => {
      dispatch(setTocDetail(null));
    };
  }, []);

  if (!data) return <div />;

  return (
    <div className="toc_update__wrapper">
      <div className="toc_update__header">
        <Typography
          variant="h5"
          className="cui_light_text"
          data-cy="toc-update-heading"
        >
          {t("toc_update.heading")}
        </Typography>
        <Avatar color="primary">
          <IconButton onClick={handleDelete} data-cy="toc-delete-icon">
            <DeleteIcon />
          </IconButton>
        </Avatar>
      </div>
      <Paper className="toc_update__form">
        <TocForm
          isEdit
          onSubmit={handleTocFormSubmit}
          equipmentRecord={
            data.equipmentRecord
              ? createEquipmentRecordSerialNoDropdownItem({
                  ...data.equipmentRecord,
                  equipmentRecord: data.equipmentRecord,
                })
              : null
          }
          serialNumber={data.serialNumber ?? ""}
          airport={createAirportDropdownItem(data.airport)}
          serviceOrganisation={createSalesOrganisationDropdownItem(
            data.salesOrganisationService
          )}
          assignee={
            data.assignee ? createPeopleDropdownItem(data.assignee) : null
          }
          technician={
            data.technician ? createPeopleDropdownItem(data.technician) : null
          }
          ifactor={TOC_FILTER_IFACTOR_OPTIONS.find(
            (item) => item.id === data.indiceFactor
          )}
          errorCodes={data.errorCodes ?? ""}
          payer={createTechnicianOnCallTypeDropdownItem(
            data.technicianOnCallType
          )}
          serviceActivity={createServiceActivityDropdownItem(
            data.serviceActivity
          )}
          unitOperationalStatus={
            data.unitOperationalStatus
              ? createUnitOperationalStatusDropdownItem(
                  data.unitOperationalStatus
                )
              : null
          }
          tags={data.tags.map((tag) =>
            createTechnicianOnCallTagDropdownItem(tag)
          )}
          originalTitle={data.originalTitle ?? data.title}
          originalDescription={data.originalDescription ?? data.description}
          mainContact={
            data.mainContact && !data.mainContact.disabled
              ? createContactDropdownItem(data.mainContact)
              : null
          }
          contacts={data.contacts
            .filter((contact) => !contact.disabled)
            .map((contact) => createContactDropdownItem(contact))}
          customer={createCustomerDropdownItem(data.customer)}
          thirdPartyName={data.thirdPartyName ?? ""}
          thirdPartyRef={data.thirdPartyRef ?? ""}
          confidential={data.confidential}
        />
      </Paper>
    </div>
  );
}

export default TocUpdate;
