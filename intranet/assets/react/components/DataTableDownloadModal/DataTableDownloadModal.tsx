import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import Modal from "../Modal/Modal";
import { IDataTableHeaderItem } from "../../types/IDataTableHeaderItem";
import {
  DATA_TABLE_DOWNLOAD_OPTIONS,
  DATA_TABLE_DOWNLOAD_STRATEGY,
  DOWNLOAD_FILE_STRATEGY,
  DOWNLOAD_FILE_TYPE,
} from "../../constants/constants";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import { validate } from "../../model/form/data_table_download_form/validation";
import { IDataTableDownloadFormData } from "../../types/IDataTableDownloadFormData";
import {
  convertFilterSubmissionDataToFormData,
  downloadCsvFile,
  downloadXlsxFile,
} from "../../utils/utils";
import { IGenericFilterFormData } from "../../types/IGenericFilterFormData";
import { useAppSelector } from "../../hooks/hooks";

interface IProps {
  headers: Array<IDataTableHeaderItem>;
  isOpen: boolean;
  onClose: () => void;
  fetchDownloadData?: (
    data: IGenericFilterFormData
  ) => Promise<Array<Array<string | null>>>;
}

const formName = "data_table_download_form";

type IWrappedProps = IProps &
  InjectedFormProps<IDataTableDownloadFormData, IProps>;

function DataTableDownloadModal(props: IWrappedProps) {
  const {
    headers,
    isOpen,
    onClose,
    fetchDownloadData,
    submitting,
    handleSubmit,
    submitFailed,
    invalid,
  } = props;

  const { filters } = useAppSelector((state) => state.dataTable);

  const onSubmit = async (values: IDataTableDownloadFormData) => {
    if (!fetchDownloadData) return;
    const newFilters = convertFilterSubmissionDataToFormData(filters);
    const textRows = await fetchDownloadData(
      values.strategy?.value === DOWNLOAD_FILE_STRATEGY.all ? {} : newFilters
    );
    if (values.format?.value === DOWNLOAD_FILE_TYPE.csv) {
      downloadCsvFile({ headers, textRows, filename: values.filename ?? "" });
    } else {
      downloadXlsxFile({
        headers,
        textRows,
        filename: values.filename ?? "",
      });
    }
    onClose();
  };

  return (
    <Modal
      className="data_table_download_modal__wrapper"
      title={Translator.trans("data_table.export.title")}
      isOpen={isOpen}
      onClose={onClose}
    >
      <form noValidate onSubmit={handleSubmit(onSubmit)}>
        <GenericFormComponent
          type="Field"
          label={Translator.trans("data_table.export.file_name.title")}
          placeholder={Translator.trans(
            "data_table.export.file_name.placeholder"
          )}
          name="filename"
          required
        />
        <GenericFormComponent
          type="SingleSelectStaticDropdown"
          label={Translator.trans("data_table.export.format.title")}
          placeholder={Translator.trans("data_table.export.format.placeholder")}
          name="format"
          list={DATA_TABLE_DOWNLOAD_OPTIONS}
          required
        />
        <GenericFormComponent
          type="SingleSelectStaticDropdown"
          label={Translator.trans("data_table.export.strategy.title")}
          placeholder={Translator.trans(
            "data_table.export.strategy.placeholder"
          )}
          name="strategy"
          list={DATA_TABLE_DOWNLOAD_STRATEGY}
          required
        />
        <button
          className="btn btn-primary mt-3"
          type="submit"
          disabled={submitting || (submitFailed && invalid)}
        >
          {Translator.trans("data_table.submit")}
        </button>
      </form>
    </Modal>
  );
}

export default reduxForm<IDataTableDownloadFormData, IProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(DataTableDownloadModal);
