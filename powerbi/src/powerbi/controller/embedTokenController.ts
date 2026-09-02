import { Request, Response } from "express";
import defaultConfig from "../constants/config.ts";
import * as EmbedConfigService from "../services/embedConfigService.ts";
import { validateConfig } from "../utils/utils.ts";
import * as AuthService from "../../common/services/authService.ts";

export const getEmbedToken = async (req: Request, res: Response) => {
  try {
    // Validate whether all the required configurations are provided in config.json
    const config = { ...defaultConfig };
    // reportId is given as query parameter
    config.reportId = req.query.reportId?.toString();
    // workspaceId is given as an optional query parameter, falling back to the env-configured workspace
    config.workspaceId =
      req.query.workspaceId?.toString() ?? defaultConfig.workspaceId;

    const token = req.headers.authorization?.split(" ")[1];
    if (!token) {
      res.status(401).send({
        error: "API Token missing",
      });
      return;
    }

    // check if user is authenticated against our API, with a valid token
    const isAuth = await AuthService.isAuthenticated(token);
    if (!isAuth) {
      res.status(403).send({
        error: "Token not valid or expired",
      });
      return;
    }

    const configCheckResult = validateConfig(config);
    if (configCheckResult) {
      res.status(400).send({
        error: configCheckResult,
      });
      return;
    }
    // Get the details like Embed URL, Access token and Expiry
    const result = await EmbedConfigService.getEmbedInfo(config);
    console.info("reportId: ", config.reportId);
    console.info("result: ", result);

    // result.status specified the statusCode that will be sent along with the result object
    res.status(result.status).send(result);
  } catch (error) {
    console.error("Error when trying to get a token from powerbi api :", error);
    res.status(500).send({
      error: "Internal error",
    });
  }
};
