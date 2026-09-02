import { Typography, TypographyVariant } from "@mui/material";
import React, { ErrorInfo } from "react";
import "./ErrorBoundary.css";
import { withTranslation, WithTranslation } from "react-i18next";
import { resetWebsite } from "../../utils/utils";

type IProps = WithTranslation & {
  children: React.ReactNode;
  variant?: TypographyVariant;
};

type IState = {
  hasError: boolean;
};

class ErrorBoundary extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError() {
    return { hasError: true };
  }

  async componentDidCatch(error: Error, info: ErrorInfo) {
    console.error("Error Bounday Catch:", error, info);
    await resetWebsite();
    window.location.reload();
  }

  render() {
    const { children, variant = "body2", t } = this.props;
    const { hasError } = this.state;

    if (hasError) {
      return (
        <div className="error_boundary__wrapper">
          <Typography variant={variant}>
            {t("common.error.general_error")}
          </Typography>
        </div>
      );
    }

    return children;
  }
}
export default withTranslation()(ErrorBoundary);
