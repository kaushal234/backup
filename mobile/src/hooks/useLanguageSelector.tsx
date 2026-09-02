import { useState } from "react";
import { useTranslation } from "react-i18next";
import { LANGUAGES } from "../constants/constants";
import { IMenuItem } from "../@type/IMenuItem";

const getSavedLanguage = () => {
  return localStorage.getItem("i18nextLng") ?? LANGUAGES[0];
};

const useLanguageSelector = (): Array<IMenuItem> => {
  const { t } = useTranslation();
  const [selectedLanguage, setSelectedLanguage] = useState(getSavedLanguage());
  const { i18n } = useTranslation();

  const handleLanguageChange = (language: string) => {
    setSelectedLanguage(language);
    i18n.changeLanguage(language);
  };

  return LANGUAGES.map((language) => ({
    type: "IMenuItem",
    text: `${t(`language.${language}`)} (${language})`,
    onClick: () => handleLanguageChange(language),
    selected: selectedLanguage === language,
    dataCy: `language-selector-${language}`,
  }));
};

export default useLanguageSelector;
