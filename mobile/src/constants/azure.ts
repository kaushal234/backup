import { Configuration, LogLevel, RedirectRequest } from "@azure/msal-browser";

const AZURE_REDIRECT_URL =
  process.env.REACT_APP_AZURE_REDIRECT_URL || `${window.location.origin}/azure`;

export const msalConfig: Configuration = {
  auth: {
    clientId: process.env.REACT_APP_AZURE_CLIENT_ID ?? "",
    authority: `https://login.microsoftonline.com/${process.env.REACT_APP_AZURE_TENANT_ID}`,
    redirectUri: AZURE_REDIRECT_URL,
    navigateToLoginRequestUrl: false,
  },
  cache: {
    cacheLocation: "localStorage",
    storeAuthStateInCookie: false,
  },
  system: {
    loggerOptions: {
      loggerCallback: (level: LogLevel, message: string) =>
        console.info(message),
    },
  },
};

export const azureScopes = ["openid", "profile", "email"];

export const loginRequest: RedirectRequest = {
  scopes: azureScopes,
  redirectUri: AZURE_REDIRECT_URL,
};
