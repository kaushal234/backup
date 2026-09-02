import React from "react";
import { ThemeProvider, createTheme } from "@mui/material/styles";
import "@fontsource/roboto/300.css";
import "@fontsource/roboto/400.css";
import "@fontsource/roboto/500.css";
import "@fontsource/roboto/700.css";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";

interface IProps {
  children: any;
}

const theme = createTheme({
  palette: {
    primary: {
      main: "#004F9E",
    },
  },
  cssVariables: {
    colorSchemeSelector: "class",
  },
  typography: {
    fontFamily: [
      "open sans",
      "Helvetica Neue",
      "Helvetica",
      "Arial",
      "sans-serif",
    ].join(","),
  },
});

function Initializer(props: IProps) {
  const { children } = props;

  return (
    <ThemeProvider theme={theme} noSsr>
      <FullScreenLoader />
      {children}
    </ThemeProvider>
  );
}

export default Initializer;
