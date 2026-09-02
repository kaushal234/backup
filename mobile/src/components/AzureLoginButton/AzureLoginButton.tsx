import React from "react";
import "./AzureLoginButton.css";
import { Button } from "@mui/material";
import { useTranslation } from "react-i18next";
import MicrosoftIcon from "@mui/icons-material/Microsoft";
import { useMsal } from "@azure/msal-react";
import { loginRequest } from "../../constants/azure";

export default function AzureLoginButton() {
  const { t } = useTranslation();
  const { instance } = useMsal();

  const handleSignIn = () => {
    instance.loginRedirect(loginRequest);
  };

  return (
    <Button
      className="cui_button azure_login_button_wrapper"
      type="button"
      variant="contained"
      onClick={handleSignIn}
      data-cy="login-azure-button"
      endIcon={<MicrosoftIcon />}
    >
      {t("login.azure")}
    </Button>
  );
}
