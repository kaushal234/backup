import express from "express";

// Prepare server PowerBI files
// Redirect JS PowerBI
const powerBiConfig = express.static("./node_modules/powerbi-client/dist/", {
  setHeaders: (res) => {
    res.setHeader(
      "Cache-Control",
      "no-store, no-cache, must-revalidate, proxy-revalidate"
    );
    res.setHeader("Pragma", "no-cache");
    res.setHeader("Expires", "0");
  },
});

export default powerBiConfig;
