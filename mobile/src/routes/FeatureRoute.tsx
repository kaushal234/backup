import React, { ReactNode, useEffect, useState } from "react";
import { useNavigate } from "react-router";
import { ROUTES } from "../constants/routes";
import { useAppDispatch, useAppSelector } from "../hooks/hooks";
import { setToastMessage } from "../redux/slices/toastSlice";
import { isFeatureAuthorised } from "../utils/utils";

interface IProps {
  children: ReactNode;
  features: Array<string>;
  requiresAll?: boolean;
}

function FeatureRoute({ children, features, requiresAll = false }: IProps) {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const userFeatures = useAppSelector((state) => state.auth.features);
  const [isAuthorised, setIsAuthorised] = useState(false);

  useEffect(() => {
    const isUserAuthorised = isFeatureAuthorised({
      userFeatures,
      features,
      requiresAll,
    });
    if (!isUserAuthorised) {
      dispatch(setToastMessage("common.access_denied"));
      navigate(ROUTES.home);
    } else {
      setIsAuthorised(true);
    }
  }, [userFeatures, features]);

  if (!isAuthorised) {
    return null;
  }

  return <div>{children}</div>;
}

export default FeatureRoute;
