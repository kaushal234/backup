import { useEffect, useState } from "react";
import { useColorScheme } from "@mui/material/styles";
import { IMenuItem } from "../@type/IMenuItem";

export type ITheme = "system" | "light" | "dark";

const Themes: Array<ITheme> = ["system", "light", "dark"];

const useThemeSelector = (): Array<IMenuItem> => {
  const { mode, setMode } = useColorScheme();
  const [dropdownValue, setDropdownValue] = useState(mode ?? "system");

  useEffect(() => {
    if (!mode) {
      setMode("system");
    }
  }, [mode]);

  const handleThemeSelect = (theme: ITheme) => {
    setDropdownValue(theme);
    setMode(theme);
  };

  return Themes.map((theme) => ({
    type: "IMenuItem",
    text: `theme.${theme}`,
    onClick: () => handleThemeSelect(theme),
    selected: dropdownValue === theme,
    dataCy: `theme-selector-${theme}`,
  }));
};

export default useThemeSelector;
