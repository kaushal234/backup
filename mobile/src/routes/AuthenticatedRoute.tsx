import React, { ReactNode, useEffect, useState } from "react";
import { useNavigate } from "react-router";
import {
  getToken,
  checkTokenExpiry,
  verifyToken,
  logoutUser,
} from "../utils/auth";
import { ROUTES } from "../constants/routes";
import { useAppDispatch } from "../hooks/hooks";
import { setToastMessage } from "../redux/slices/toastSlice";

interface IProps {
  children: ReactNode;
}

function AuthenticatedRoute({ children }: IProps) {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | undefined>();

  useEffect(() => {
    (async () => {
      const token = await getToken();
      let isUserAuthenticated = false;
      if (token) {
        const isVerified = await verifyToken(token);
        const isExpired = checkTokenExpiry(token);
        isUserAuthenticated = isVerified && !isExpired;
      }

      setIsAuthenticated(isUserAuthenticated);
      if (!isUserAuthenticated) {
        await logoutUser();
        dispatch(setToastMessage("common.login_required"));
        navigate(ROUTES.login);
      }
    })();
  }, []);

  if (isAuthenticated === undefined || !isAuthenticated) {
    return <div />;
  }

  return <div>{children}</div>;
}

export default AuthenticatedRoute;
