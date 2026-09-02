import "@mui/x-data-grid";

declare module "@mui/x-data-grid" {
  export interface BaseCheckboxPropsOverrides {
    selectedContacts: Array<string>;
    isSelectingAll: boolean;
    handleSelectAllFiltered: () => void;
  }
}
