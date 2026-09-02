import { Router } from "express";
import * as EmbedTokenController from "../controller/embedTokenController.ts";

const router = Router();

router.get("/getEmbedToken", EmbedTokenController.getEmbedToken);

export default router;
