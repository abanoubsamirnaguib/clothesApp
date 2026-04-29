// nuxt.config.ts
import pkg from "./package.json";

const enableNuxtHub = process.env.NUXT_HUB === "true";

export default defineNuxtConfig({
  // Shared hosting deployments (static files + PHP backend) do not have a Nitro/Node server.
  // Run as SPA to avoid requesting server payloads like `/_payload.json`.
  ssr: false,
  devtools: { enabled: false },

  modules: [
    "@vueuse/nuxt",
    "@nuxt/ui",
    "@nuxt/image",
    "notivue/nuxt",
    "@nuxtjs/i18n",
    "@nuxt/icon",
    ...(enableNuxtHub ? ["@nuxthub/core"] : []),
  ],

  icon: {
    // Static hosting (no Nitro server) => do not use `/api/_nuxt_icon`.
    provider: "none",
    serverBundle: false,
    clientBundle: {
      scan: true,
    },
  },

  i18n: {
    defaultLocale: "en",
    fallbackLocale: "en",
    strategy: "prefix_except_default",
    // Bundle messages to avoid runtime lazy-loading failures on shared hosting.
    lazy: false,
    vueI18n: "./i18n.config.ts",
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: "i18n_redirected",
      redirectOn: "root",
      alwaysRedirect: true,
      fallbackLocale: "en",
    },
    locales: [
      { code: "en", iso: "en-GB", name: "🇬🇧 English" },
      { code: "nb", iso: "nb-NO", name: "🇳🇴 Norsk (Bokmål)" },
      { code: "nl", iso: "nl-NL", name: "🇳🇱 Nederlands" },
      { code: "de", iso: "de-DE", name: "🇩🇪 Deutsch" },
    ],
  },

  notivue: {
    position: "top-center",
    limit: 3,
    notifications: { global: { duration: 3000 } },
  },

  css: ["notivue/notification.css", "notivue/animations.css"],

  runtimeConfig: {
    laravelApiUrl: process.env.LARAVEL_API_URL || "",
    huggingface: {
      space: process.env.HF_TRYON_SPACE || "yisol/IDM-VTON",
      token: process.env.HF_TOKEN || "",
    },
    public: {
      version: pkg.version,
      // Expose API base to the browser for static deployments.
      laravelApiUrl: process.env.LARAVEL_API_URL || "",
    },
  },

  routeRules: {
    "/": { prerender: true },
    "/categories": { swr: 3600 },
    "/favorites": { swr: 600 },
  },

  nitro: {
    preset: "static",
    prerender: { routes: ["/sitemap.xml", "/robots.txt"] },
  },

  ...(enableNuxtHub
    ? {
        hub: {
          cache: process.env.NODE_ENV === "production"
            ? {
                driver: "cloudflare-kv-binding",
                binding: "CACHE",
              }
            : {
                driver: "memory",
              },
        },
      }
    : {}),

  experimental: {
    // Prevent the browser from requesting `/_payload.json` files on navigation/prefetch.
    payloadExtraction: false,
  },

  compatibilityDate: "2025-01-01",
});
