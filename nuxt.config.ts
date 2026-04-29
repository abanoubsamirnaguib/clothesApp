// nuxt.config.ts
import pkg from "./package.json";

export default defineNuxtConfig({
  // Shared hosting deployments (static files + PHP backend) do not have a Nitro/Node server.
  // Run as SPA to avoid requesting server payloads like `/_payload.json`.
  ssr: false,
  devtools: { enabled: false },

  modules: ["@vueuse/nuxt", "@nuxt/ui", "@nuxt/image", "notivue/nuxt", "@nuxtjs/i18n", "@nuxthub/core"],

  i18n: {
    defaultLocale: "en",
    strategy: "prefix_except_default",
    // Locale files live in `i18n/locales/*`.
    langDir: "i18n/locales",
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: "i18n_redirected",
      redirectOn: "root",
      alwaysRedirect: true,
    },
    locales: [
      { code: "en", iso: "en-GB", file: "en-GB.json", name: "🇬🇧 English" },
      { code: "nb", iso: "nb-NO", file: "nb-NO.json", name: "🇳🇴 Norsk (Bokmål)" },
      { code: "nl", iso: "nl-NL", file: "nl-NL.json", name: "🇳🇱 Nederlands" },
      { code: "de", iso: "de-DE", file: "de-DE.json", name: "🇩🇪 Deutsch" },
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

  experimental: {
    // Prevent the browser from requesting `/_payload.json` files on navigation/prefetch.
    payloadExtraction: false,
  },

  compatibilityDate: "2025-01-01",
});
