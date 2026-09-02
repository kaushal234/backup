import React, { ReactNode, useEffect, useState } from "react";
import { useNavigate } from "react-router";
import { getToken, checkTokenExpiry, verifyToken } from "../utils/auth";
import { ROUTES } from "../constants/routes";

interface IProps {
  children: ReactNode;
}

function PreLoginRoute({ children }: IProps) {
  const navigate = useNavigate();
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
      if (isUserAuthenticated) {
        navigate(ROUTES.home);
      }
    })();
  }, []);

  if (isAuthenticated === undefined || isAuthenticated) {
    return <div />;
  }

  return <div>{children}</div>;
}

export default PreLoginRoute;
