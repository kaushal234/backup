import React, { ReactNode } from "react";
import { MsalProvider } from "@azure/msal-react";
import { PublicClientApplication } from "@azure/msal-browser";
import { msalConfig } from "../../constants/azure";

interface IProps {
  children: ReactNode;
}

const msalInstance = new PublicClientApplication(msalConfig);

function AzureProvider(props: IProps) {
  const { children } = props;
  return <MsalProvider instance={msalInstance}>{children}</MsalProvider>;
}

export default AzureProvider;
