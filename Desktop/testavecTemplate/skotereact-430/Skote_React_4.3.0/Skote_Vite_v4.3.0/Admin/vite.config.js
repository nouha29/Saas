import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  esbuild: {
    jsxFactory: "h",
    jsxFragment: "Fragment",
    jsx: "automatic",
    jsxDev: true,
  },
  server: {
    proxy: {
      "^/api/": {
        target: "http://127.0.0.1:8000",
        changeOrigin: true,
        rewrite: (path) => {
          console.log("Proxy rewriting:", path, "to", path);
          return path; // Preserve /api/verify-template and /api/extract-transactions
        },
        configure: (proxy, _options) => {
          proxy.on("error", (err, _req, _res) => {
            console.error("Proxy error:", err);
          });
          proxy.on("proxyReq", (proxyReq, req, _res) => {
            console.log("Proxy request:", req.url);
          });
        },
      },
    },
  },
});