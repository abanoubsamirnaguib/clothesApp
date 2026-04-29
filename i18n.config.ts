import en from "./i18n/locales/en-GB.json";
import nb from "./i18n/locales/nb-NO.json";
import nl from "./i18n/locales/nl-NL.json";
import de from "./i18n/locales/de-DE.json";

export default defineI18nConfig(() => ({
  legacy: false,
  locale: "en",
  fallbackLocale: "en",
  messages: {
    en,
    "en-GB": en,
    "en-US": en,
    nb,
    "nb-NO": nb,
    nl,
    "nl-NL": nl,
    de,
    "de-DE": de,
  },
}));

