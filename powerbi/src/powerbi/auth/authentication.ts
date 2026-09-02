// Use MSAL.js for authentication
import * as msal from "@azure/msal-node";
import { IConfig } from "../@types/IConfig.ts";

export const getAccessToken = async (config: IConfig) => {
  const msalConfig: msal.Configuration = {
    auth: {
      clientId: config.clientId ?? "",
      authority: `${config.authorityUrl}${config.tenantId}`,
    },
  };

  // Service Principal auth is the recommended by Microsoft to achieve App Owns Data Power BI embedding
  if (config.authenticationMode?.toLowerCase() === "serviceprincipal") {
    msalConfig.auth.clientSecret = config.clientSecret;
    const clientApplication = new msal.ConfidentialClientApplication(
      msalConfig
    );

    const clientCredentialRequest = {
      scopes: [config.scopeBase ?? ""],
    };

    return clientApplication.acquireTokenByClientCredential(
      clientCredentialRequest
    );
  }
  return null;
};
