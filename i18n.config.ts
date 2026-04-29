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
    "en-gb": en,
    "en-us": en,
    "en_GB": en,
    "en_US": en,
    nb,
    "nb-NO": nb,
    "nb-no": nb,
    "nb_NO": nb,
    nl,
    "nl-NL": nl,
    "nl-nl": nl,
    "nl_NL": nl,
    de,
    "de-DE": de,
    "de-de": de,
    "de_DE": de,
  },
}));

