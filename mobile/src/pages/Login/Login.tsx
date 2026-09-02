import React from "react";
import Button from "@mui/material/Button";
import "./Login.css";
import { useNavigate } from "react-router";
import { useTranslation } from "react-i18next";
import { Divider, Paper, Typography } from "@mui/material";
import FormGroup from "@mui/material/FormGroup";
import FormControlLabel from "@mui/material/FormControlLabel";
import Checkbox from "@mui/material/Checkbox";
import { StatusCodes } from "http-status-codes";
import { loginApi } from "../../api/login";
import { useAppDispatch } from "../../hooks/hooks";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { setupUser } from "../../utils/auth";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import Logo from "../../components/Logo/Logo";
import { ROUTES } from "../../constants/routes";
import { useFormField } from "../../hooks/useFormField";
import { toastError } from "../../utils/api";
import { useDrawer } from "../../hooks/useDrawer";
import FormField from "../../components/FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";
import AzureLoginButton from "../../components/AzureLoginButton/AzureLoginButton";
import { setIsDrawerOpen } from "../../redux/slices/appBarSlice";

const validateEmail = (value: string) => {
  if (value && /\S+@\S+\.\S+/.test(value)) return "";
  return "login.email.error.required";
};

const validatePassword = (value: string) => {
  if (value && value.length > 8) return "";
  return "login.password.error.required";
};

function Login() {
  useDrawer(ROUTES.login);
  const dispatch = useAppDispatch();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const email = useFormField({
    defaultValue: "",
    validate: validateEmail,
  });

  const password = useFormField({
    defaultValue: "",
    validate: validatePassword,
  });

  const formValidator = useFormValidator([email, password]);

  const handleSignIn = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      dispatch(showMainLoader(true));
      const response = await loginApi({
        username: email.value,
        password: password.value,
      });
      if (response.status === StatusCodes.OK && response.data) {
        const { token } = response.data;
        dispatch(setIsDrawerOpen(true));
        setupUser({ token, dispatch, navigate });
      } else if (response.status === 401) {
        dispatch(setToastMessage("auth_error"));
      } else {
        toastError(dispatch, response);
      }
      dispatch(showMainLoader(false));
    }
  };

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
      }}
    >
      <div className="login__wrapper">
        <Logo className="login_logo" dataCy="login-logo" />
        <Paper elevation={1} className="login__form_wrapper">
          <FormField
            {...email.fieldProps}
            label="login.email.label"
            requiredLabel
            id="email"
            placeholder="login.email.placeholder"
            autoComplete="username"
            dataCy="login-username"
          />
          <FormField
            {...password.fieldProps}
            label="login.password.label"
            requiredLabel
            placeholder="login.password.label"
            type="password"
            id="password"
            autoComplete="current-password"
            dataCy="login-password"
          />
          <FormGroup>
            <FormControlLabel
              className="cui_label_checkbox"
              control={<Checkbox defaultChecked />}
              label={t("login.remember_me")}
            />
          </FormGroup>
          <Button
            disabled={formValidator.isSubmitDisabled}
            className="cui_button"
            style={{ maxWidth: "100%" }}
            type="submit"
            variant="contained"
            onClick={handleSignIn}
            data-cy="login-submit-button"
          >
            {t("login.submit")}
          </Button>
          <div className="login__seperator">
            <Divider />
            <Typography variant="body1">OR</Typography>
            <Divider />
          </div>
          <div>
            <AzureLoginButton />
          </div>
        </Paper>
      </div>
    </form>
  );
}

export default Login;
