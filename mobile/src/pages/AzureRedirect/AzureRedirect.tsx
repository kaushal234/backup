import React, { useEffect, useState } from "react";
import { useMsal } from "@azure/msal-react";
import { useNavigate } from "react-router";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { postAzureLogin } from "../../api/postAzureLogin";
import { azureScopes } from "../../constants/azure";
import { getToken, setupUser } from "../../utils/auth";
import { ROUTES } from "../../constants/routes";
import { setToastMessage } from "../../redux/slices/toastSlice";

export default function AzureRedirect() {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { instance, accounts } = useMsal();
  const [timeoutId, setTimeoutId] = useState<NodeJS.Timeout>();

  const handleTimeout = () => {
    dispatch(setToastMessage("common.error.general_error"));
    navigate(ROUTES.login);
  };

  const handleLoginUser = async () => {
    dispatch(showMainLoader(true));
    if (!accounts.length) {
      return;
    }
    const response = await instance.acquireTokenSilent({
      scopes: azureScopes,
      account: accounts[0],
    });
    const loginResponse = await postAzureLogin({ token: response.accessToken });
    if (loginResponse.data?.token) {
      setupUser({ token: loginResponse.data?.token ?? "", dispatch, navigate });
    } else {
      handleTimeout();
    }
  };

  const handleRedirect = async () => {
    const isAuthenticated = await getToken();
    if (isAuthenticated) {
      navigate(ROUTES.home);
    } else {
      if (!timeoutId) {
        setTimeoutId(setTimeout(handleTimeout, 60000));
      }
      handleLoginUser();
    }
  };

  useEffect(() => {
    handleRedirect();
    return () => {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      dispatch(showMainLoader(false));
    };
  }, [accounts]);

  return <div />;
}
