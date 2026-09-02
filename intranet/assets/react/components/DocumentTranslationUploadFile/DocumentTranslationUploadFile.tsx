import React from "react";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { StatusCodes } from "http-status-codes";
import { sendDocumentToTranslate } from "../../api/sendDocumentToTranslate";
import {
  DOCUMENT_TRANSLATOR_FILE_TYPES,
  FORMALITY_OPTIONS,
  LANGUAGES_OPTIONS,
} from "../../constants/constants";
import { useAppDispatch } from "../../hooks/hooks";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";
import { IDocumentTranslatorFormData } from "../../types/IDocumentTranslatorFormData";
import { validate } from "../../model/form/document_translator/validation";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

const formName = "document_translation_form";

interface IProps {
  onUploaded?: () => void;
}

type IWrappedProps = IProps &
  InjectedFormProps<IDocumentTranslatorFormData, IProps>;

function DocumentTranslationUploadFile(props: IWrappedProps) {
  const { onUploaded, submitting, valid, handleSubmit } = props;
  const dispatch = useAppDispatch();

  const onSubmit = async (values: IDocumentTranslatorFormData) => {
    if (values.file?.[0]) {
      dispatch(showGlobalLoader(true));
      const response = await sendDocumentToTranslate({
        targetLang: values.language?.value ?? "",
        file: values.file?.[0],
        formality: values.formality?.value ?? "",
      });
      dispatch(showGlobalLoader(false));
      if (response.status === StatusCodes.OK && response.data) {
        onUploaded?.();
      }
    }
  };

  return (
    <div>
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="mb-3">
          <GenericFormComponent
            type="FileInput"
            label={Translator.trans(
              "global_chat.document_translator.file.title"
            )}
            name="file"
            required
            accept={DOCUMENT_TRANSLATOR_FILE_TYPES}
          />
        </div>

        <div className="mb-3">
          <GenericFormComponent
            type="SingleSelectStaticDropdown"
            label={Translator.trans(
              "global_chat.document_translator.formality.title"
            )}
            placeholder={Translator.trans(
              "global_chat.document_translator.formality.placeholder"
            )}
            name="formality"
            list={FORMALITY_OPTIONS}
            required
          />
        </div>

        <div className="mb-3">
          <GenericFormComponent
            type="SingleSelectStaticDropdown"
            label={Translator.trans(
              "global_chat.document_translator.language.title"
            )}
            placeholder={Translator.trans(
              "global_chat.document_translator.language.placeholder"
            )}
            name="language"
            list={LANGUAGES_OPTIONS}
            required
          />
        </div>

        <div className="d-flex align-items-center gap-3">
          <button
            className="btn btn-primary mt-3"
            type="submit"
            disabled={submitting || !valid}
          >
            {Translator.trans("upload.button_upload")}
          </button>
        </div>
      </form>
    </div>
  );
}

export default reduxForm<IDocumentTranslatorFormData, IProps>({
  form: formName,
  initialValues: {
    formality: FORMALITY_OPTIONS[0],
    language: LANGUAGES_OPTIONS[6],
  },
  enableReinitialize: true,
  validate,
})(DocumentTranslationUploadFile);
