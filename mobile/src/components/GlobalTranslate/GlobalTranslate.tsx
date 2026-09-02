import React from "react";
import TranslateIcon from "@mui/icons-material/Translate";
import { ToggleButton } from "@mui/material";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setShowTranslation } from "../../redux/slices/translationSlice";
import "./GlobalTranslate.css";

export default function GlobalTranslate() {
  const { showTranslation, showTranslationIcon } = useAppSelector(
    (state) => state.translation
  );
  const dispatch = useAppDispatch();

  const handleClick = () => {
    dispatch(setShowTranslation(!showTranslation));
  };

  if (!showTranslationIcon) {
    return null;
  }

  return (
    <ToggleButton
      className="global_translate__toggle_wrapper"
      value="check"
      selected={showTranslation}
      onChange={handleClick}
    >
      <TranslateIcon />
    </ToggleButton>
  );
}
