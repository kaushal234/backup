import { IConfig } from "../@types/IConfig.ts";

const config: IConfig = {};

config.authenticationMode = process.env.AUTHENTICATION_MODE;
config.authorityUrl = process.env.AUTHORITY_URL;
config.scopeBase = process.env.SCOPE_BASE;
config.powerBiApiUrl = process.env.POWERBI_API_URL;
config.clientId = process.env.CLIENT_ID;
config.workspaceId = process.env.WORKSPACE_ID;
config.clientSecret = process.env.CLIENT_SECRET;
config.tenantId = process.env.TENANT_ID;

export default config;
