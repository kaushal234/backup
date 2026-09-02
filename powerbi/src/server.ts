import "./env.ts";
import express from "express";
import cors from "cors";
import powerbiRoutes from "./powerbi/routes/index.ts";
import cacheMiddleware from "./powerbi/middleware/cacheMiddleware.ts";
import powerBiConfig from "./powerbi/config/powerBiConfig.ts";

const app = express();

app.use(cacheMiddleware);

app.use("/js", powerBiConfig);

app.use(cors());

app.use(express.json());

app.use(
  express.urlencoded({
    extended: true,
  })
);

const port = process.env.PORT || 9500;

app.use("/powerbi", powerbiRoutes);

app.listen(port, () => console.info(`Listening on port ${port}`));

export default app;
