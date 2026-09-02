import { useEffect } from "react";
import { useAppDispatch, useAppSelector } from "./hooks";
import { setShowTranslationIcon } from "../redux/slices/translationSlice";

const useGlobalTranslate = (showIcon = true) => {
  const dispatch = useAppDispatch();

  const showTranslation = useAppSelector(
    (state) => state.translation.showTranslation
  );

  useEffect(() => {
    if (showIcon) {
      dispatch(setShowTranslationIcon(true));
    }
    return () => {
      dispatch(setShowTranslationIcon(false));
    };
  }, []);

  return showTranslation;
};

export default useGlobalTranslate;
