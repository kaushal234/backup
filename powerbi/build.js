import esbuild from "esbuild";

esbuild
  .build({
    entryPoints: ["src/server.ts"],
    bundle: true,
    platform: "node",
    target: "es2016",
    outfile: "dist/server.cjs",
  })
  .catch(() => process.exit(1));
