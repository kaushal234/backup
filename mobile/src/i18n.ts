import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import LanguageDetector from "i18next-browser-languagedetector";
import Backend from "i18next-http-backend";
import jsYaml from "js-yaml";
import gettextParser from "gettext-parser";
import { LANGUAGE_FILE_NAMES, LANGUAGES } from "./constants/constants";

interface IDynamicTranslation {
  [key: string]: string | IDynamicTranslation;
}

const mergeDeep = (
  target: IDynamicTranslation,
  source: IDynamicTranslation
) => {
  Object.keys(source).forEach((key) => {
    if (source[key] instanceof Object && key in target) {
      const newTarget = target[key] as IDynamicTranslation;
      const newSource = source[key] as IDynamicTranslation;
      Object.assign(source[key], mergeDeep(newTarget, newSource));
    }
  });
  return { ...target, ...source };
};

const mergeTranslations = (translationsArray: Array<IDynamicTranslation>) => {
  return translationsArray.reduce((acc, translations) => {
    return mergeDeep(acc, translations);
  }, {});
};

const translateYaml = (results: string[]) => {
  return results.map((result) => jsYaml.load(result) as IDynamicTranslation);
};

const translatePo = (results: string[]) => {
  return results.map((result) => {
    const parsed = gettextParser.po.parse(result);
    const json: IDynamicTranslation = {};
    Object.entries(parsed.translations[""]).forEach(([key, value]) => {
      if (key) {
        const [jsonValue] = value.msgstr;
        json[key] = jsonValue;
      }
    });
    return json;
  });
};

i18n
  .use(initReactI18next)
  .use(LanguageDetector)
  .use(Backend)
  .init({
    backend: {
      loadPath: "/locales/{{lng}}/{{ns}}",
      request: async (
        options: unknown,
        loadPathUrl: string,
        payload: unknown,
        callback: (
          error: null | unknown,
          data: { data?: IDynamicTranslation; status: number }
        ) => void
      ) => {
        try {
          const lng = loadPathUrl.split("/")[2] ?? "en";
          const extension = lng === "en" ? "yaml" : "po";
          const urls = LANGUAGE_FILE_NAMES.map(
            (fileName) => `/locales/${lng}/${fileName}.${extension}`
          );

          const results = await Promise.all(
            urls.map((url) => fetch(url).then((res) => res.text()))
          );

          const translationsArray =
            lng === "en" ? translateYaml(results) : translatePo(results);

          const mergedTranslations = mergeTranslations(translationsArray);
          callback(null, { data: mergedTranslations, status: 200 });
        } catch (err) {
          callback(err, { status: 500 });
        }
      },
    },
    supportedLngs: LANGUAGES,
    fallbackLng: "en",
  });

export default i18n;
