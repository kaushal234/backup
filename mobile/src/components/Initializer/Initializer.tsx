import React, { useEffect } from "react";
import Toast from "../Toast/Toast";
import MainLoader from "../MainLoader/MainLoader";
import RouteMap from "../../routes/RouteMap";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { LS_KEYS } from "../../constants/constants";
import {
  setIsServiceOrganisationPopulated,
  setTocFilters,
} from "../../redux/slices/tocFilterSlice";
import ConfirmationPopUp from "../ConfirmationPopUp/ConfirmationPopUp";
import { createServiceOrganisationDropdownItem } from "../../utils/dropdown/serviceOrganisation";
import { setErFilters } from "../../redux/slices/erFilterSlice";
import { getLocationById } from "../../api/getLocationById";
import { extractLocationId } from "../../utils/utils";

const usePopulateTocFilters = () => {
  const dispatch = useAppDispatch();

  useEffect(() => {
    const savedFilters = localStorage.getItem(LS_KEYS.toc_filters);
    if (savedFilters) {
      dispatch(setTocFilters(JSON.parse(savedFilters)));
    }
  }, []);
};

const usePopulateServiceOrganisationFilter = () => {
  const dispatch = useAppDispatch();
  const userInfo = useAppSelector((state) => state.auth.userInfo);
  const tocFilters = useAppSelector((state) => state.tocFilter.filters);
  const isServiceOrganisationPopulated = useAppSelector(
    (state) => state.tocFilter.isServiceOrganisationPopulated
  );

  const populateServiceOrganisation = async () => {
    if (!isServiceOrganisationPopulated && userInfo) {
      const match = await getLocationById({
        id: extractLocationId(userInfo?.businessUnit?.location["@id"]),
      });
      if (match.data) {
        dispatch(
          setTocFilters({
            ...tocFilters,
            serviceOrganisation: [
              createServiceOrganisationDropdownItem(match.data),
            ],
          })
        );
        dispatch(setIsServiceOrganisationPopulated(true));
      }
    }
  };

  useEffect(() => {
    populateServiceOrganisation();
  }, [userInfo, isServiceOrganisationPopulated]);
};

const usePopulateErFilters = () => {
  const dispatch = useAppDispatch();

  useEffect(() => {
    const savedFilters = localStorage.getItem(LS_KEYS.er_filters);
    if (savedFilters) {
      dispatch(setErFilters(JSON.parse(savedFilters)));
    }
  }, []);
};

const useServiceWorker = () => {
  useEffect(() => {
    if (navigator.serviceWorker) {
      navigator.serviceWorker.addEventListener("controllerchange", () => {
        window.location.reload();
      });
    }
  }, []);
};

function Initializer() {
  usePopulateTocFilters();
  usePopulateServiceOrganisationFilter();
  usePopulateErFilters();
  useServiceWorker();

  return (
    <>
      <ConfirmationPopUp />
      <Toast />
      <MainLoader />
      <RouteMap />
    </>
  );
}

export default Initializer;
