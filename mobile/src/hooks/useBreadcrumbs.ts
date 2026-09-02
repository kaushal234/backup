import { useEffect } from "react";
import { IBreadcrumb } from "../@type/IBreadcrumb";
import { useAppDispatch } from "./hooks";
import {
  setBreadcrumbs,
  resetBreadcrumbs,
} from "../redux/slices/breadcrumbSlice";

export const useBreadcrumbs = (breadcrumbs: Array<IBreadcrumb>) => {
  const dispatch = useAppDispatch();

  useEffect(() => {
    dispatch(setBreadcrumbs(breadcrumbs));
    return () => {
      dispatch(resetBreadcrumbs());
    };
  }, []);
};
