import React from "react";
import "./colors.css";
import "./variables.css";
import { Provider } from "react-redux";
import CssBaseline from "@mui/material/CssBaseline";
import { ThemeProvider, createTheme } from "@mui/material/styles";
import { SnackbarProvider } from "notistack";
import { store } from "./redux/store";
import "@fontsource/roboto/300.css";
import "@fontsource/roboto/400.css";
import "@fontsource/roboto/500.css";
import "@fontsource/roboto/700.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import Initializer from "./components/Initializer/Initializer";
import ToastCloseIcon from "./components/ToastCloseIcon/ToastCloseIcon";
import AzureProvider from "./components/AzureProvider/AzureProvider";
import ErrorBoundary from "./components/ErrorBoundary/ErrorBoundary";

const theme = createTheme({
  colorSchemes: {
    dark: true,
  },
  cssVariables: {
    colorSchemeSelector: "class",
  },
});

function App() {
  return (
    <ErrorBoundary>
      <AzureProvider>
        <ThemeProvider theme={theme} noSsr>
          <CssBaseline enableColorScheme />
          <Provider store={store}>
            <SnackbarProvider maxSnack={3} action={ToastCloseIcon}>
              <Initializer />
            </SnackbarProvider>
          </Provider>
        </ThemeProvider>
      </AzureProvider>
    </ErrorBoundary>
  );
}

export default App;
