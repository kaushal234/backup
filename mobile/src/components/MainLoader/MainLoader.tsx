import React from "react";
import { Backdrop, CircularProgress } from "@mui/material";
import { useAppSelector } from "../../hooks/hooks";

function MainLoader() {
  const isLoading = useAppSelector((store) => store.loader.isLoading);

  if (!isLoading) {
    return <div />;
  }

  return (
    <Backdrop
      sx={(theme) => ({ color: "#fff", zIndex: theme.zIndex.modal + 1 })}
      open
    >
      <CircularProgress color="inherit" data-cy="main-loader" />
    </Backdrop>
  );
}

export default MainLoader;
