import React, { useEffect } from "react";
import "./CommentPopUp.css";
import { Button, Dialog, Typography } from "@mui/material";
import { t } from "i18next";
import ExitToAppIcon from "@mui/icons-material/ExitToApp";
import OutputIcon from "@mui/icons-material/Output";
import FormFileUpload from "../FormFileUpload/FormFileUpload";
import { useFormFileUpload } from "../../hooks/useFormFileUpload";
import FormRadioButtons from "../FormRadioButtons/FormRadioButtons";
import { useFormRadioButtons } from "../../hooks/useFormRadioButtons";
import { IRadioButton } from "../../@type/IRadioButton";
import { postCommentById } from "../../api/postCommentById";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import FormRichTextField from "../FormRichTextField/FormRichTextField";
import { useFormRichTextField } from "../../hooks/useFormRichTextField";
import { useConfirmation } from "../../hooks/useConfirmation";
import { ICommentType } from "../../@type/ICommentType";
import { toastError } from "../../utils/api";
import {
  COMMENT_TYPES,
  FACTORY_FLAG,
  IDB_DATABASE,
} from "../../constants/constants";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { useFormSwitch } from "../../hooks/useFormSwitch";
import FormSwitch from "../FormSwitch/FormSwitch";
import { useFormValidator } from "../../hooks/useFormValidator";
import FeatureComponent from "../FeatureComponent/FeatureComponent";

const TYPE_RADIO_OPTIONS_CONFIDENTIAL: Array<IRadioButton> = [
  {
    id: "internal_notification",
    text: "logs_preview.type.option.internal_notification",
  },
  { id: "internal", text: "logs_preview.type.option.internal" },
];

const TYPE_RADIO_OPTIONS: Array<IRadioButton> = [
  ...TYPE_RADIO_OPTIONS_CONFIDENTIAL,
  { id: "external", text: "logs_preview.type.option.external" },
];

const getMetaData = (data: {
  oldValue: boolean;
  newValue: boolean;
  showFactoryFlag: boolean;
}) => {
  if (!data.newValue || !data.showFactoryFlag) {
    return null;
  }
  return {
    metadata: {
      factoryFlag: data.oldValue ? FACTORY_FLAG.close : FACTORY_FLAG.open,
    },
  };
};

interface IProps {
  title: string;
  iri: string;
  linkedTocIri?: string;
  commentType: ICommentType;
  isOpen: boolean;
  onClose: (isDataUpdated: boolean) => void;
  factoryFlag?: boolean;
  showFactoryFlag: boolean;
  disableFactoryFlag?: boolean;
  flipFactoryFlag?: boolean;
  confidentialToc: boolean;
}

export default function CommentPopUp(props: IProps) {
  const {
    title,
    iri,
    linkedTocIri = "",
    commentType,
    isOpen,
    onClose,
    factoryFlag: factoryFlagDefault = false,
    showFactoryFlag,
    disableFactoryFlag = false,
    flipFactoryFlag = false,
    confidentialToc,
  } = props;
  const dispatch = useAppDispatch();
  const { confirmation } = useConfirmation();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const comment = useFormRichTextField({
    defaultValue: "",
    requiredError: "logs_preview.comment.error",
  });

  const file = useFormFileUpload({
    defaultValue: null,
    accept: "comment",
    maxSize: 15 * 1024 * 1024,
  });

  const type = useFormRadioButtons({
    defaultValue: "internal_notification",
    list: confidentialToc
      ? TYPE_RADIO_OPTIONS_CONFIDENTIAL
      : TYPE_RADIO_OPTIONS,
  });

  const factoryFlag = useFormSwitch({
    defaultValue: flipFactoryFlag,
  });

  const handleClose = (isDataUpdated?: boolean) => {
    onClose(!!isDataUpdated);
    comment.reset();
    file.reset();
    type.reset();
    factoryFlag.reset();
  };

  const formValidator = useFormValidator([comment]);

  const handleComment = async () => {
    formValidator.touchAll();
    if (formValidator.isFormErrorFree) {
      let confirmationResponse = true;
      if (type.value === TYPE_RADIO_OPTIONS[2].id) {
        confirmationResponse = await confirmation(
          "logs_preview.confirmation.title",
          "logs_preview.confirmation.description",
          comment.value
        );
      }
      if (confirmationResponse) {
        dispatch(showMainLoader(true));
        const params = {
          "@id":
            commentType === COMMENT_TYPES.TOC_FROM_CSR ? linkedTocIri : iri,
          comment: comment.value,
          public: type.value === "external",
          ...(file.value?.[0] && { file: file.value[0].file }),
          ...(file.value?.[0] && {
            fileDescription: file.value[0].description,
          }),
          ...getMetaData({
            oldValue: factoryFlagDefault,
            newValue: factoryFlag.value,
            showFactoryFlag,
          }),
          ...(type.value === TYPE_RADIO_OPTIONS[1].id && {
            metadata: {
              notifications: false,
            },
          }),
        };
        if (isOnline) {
          handleClose();
          const response = await postCommentById(params);
          if (!response.data) {
            toastError(dispatch, response);
          }
          handleClose(true);
        } else {
          handleClose();
          await registerSyncEventWithApiPayload({
            eventName: IDB_DATABASE.stores.sync_post_comment,
            payload: params,
          });
          dispatch(setToastMessage("logs_preview.offline"));
          dispatch(showMainLoader(false));
        }
      }
    }
  };

  useEffect(() => {
    if (factoryFlag.value) {
      type.helper.setValue(TYPE_RADIO_OPTIONS[0].id);
    }
  }, [factoryFlag.value]);

  return (
    <Dialog
      closeAfterTransition={false}
      className="comment_popup__comment_wrapper"
      onClose={() => handleClose()}
      open={isOpen}
    >
      <Typography
        className="cui_light_text"
        variant="h5"
        gutterBottom
        data-cy="add-log-popup-heading"
      >
        {t(title)}
      </Typography>
      <div className="comment_popup__comment_input_wrapper">
        <FormRichTextField
          label="logs_preview.comment.title"
          requiredLabel
          {...comment.fieldProps}
          placeholder="logs_preview.comment.placeholder"
          dataCy="add-log-popup-log"
        />
      </div>
      {showFactoryFlag && !flipFactoryFlag && (
        <FeatureComponent
          features={
            factoryFlagDefault
              ? ["FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG"]
              : ["FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG"]
          }
        >
          <div>
            <FormSwitch
              {...factoryFlag.fieldProps}
              label={
                !factoryFlagDefault
                  ? "logs_preview.factory_flag.title.open"
                  : "logs_preview.factory_flag.title.close"
              }
              dataCy="add-log-popup-factory-flag"
              disabled={disableFactoryFlag}
            />
          </div>
        </FeatureComponent>
      )}
      <div className="comment_popup__comment_input_wrapper">
        <FormFileUpload
          {...file.fieldProps}
          label="logs_preview.file.title"
          dataCy="add-log-popup-file"
        />
      </div>
      <div>
        <FormRadioButtons
          {...type.fieldProps}
          label="logs_preview.type.title"
          dataCy="add-log-popup-notification"
          disabled={factoryFlag.value}
        />
      </div>
      <div>
        <Button
          className="cui_button comment_popup__submit_button"
          variant="contained"
          endIcon={
            type.value === "external" ? <OutputIcon /> : <ExitToAppIcon />
          }
          onClick={handleComment}
          disabled={formValidator.isSubmitDisabled}
          data-cy="add-log-popup-submit"
        >
          {!flipFactoryFlag && t("logs_preview.submit.title")}
          {flipFactoryFlag &&
            (factoryFlagDefault
              ? t("logs_preview.submit.factory_flag_close")
              : t("logs_preview.submit.factory_flag_open"))}
        </Button>
      </div>
    </Dialog>
  );
}
