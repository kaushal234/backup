import React from "react";
import "./FullScreenLoader.css";
import { createPortal } from "react-dom";
import { useAppSelector } from "../../hooks/hooks";
import Loader from "../Loader";

function FullScreenLoader() {
  const isLoading = useAppSelector((state) => state.loader.isLoading);

  if (!isLoading) return null;

  return createPortal(
    <div className="full_screen_loader__backdrop">
      <div className="full_screen_loader__wrapper">
        <Loader />
      </div>
    </div>,
    document.body
  );
}

export default FullScreenLoader;
