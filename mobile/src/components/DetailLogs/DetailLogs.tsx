import React, { useState } from "react";
import "./DetailLogs.css";
import { Avatar, Chip, IconButton, Paper, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import CreateIcon from "@mui/icons-material/Create";
import FlagIcon from "@mui/icons-material/Flag";
import { formatDateTimeLong } from "../../utils/date";
import {
  getColorForWord,
  getFileNameFromPath,
  sanitize,
} from "../../utils/utils";
import CommentPopUp from "../CommentPopUp/CommentPopUp";
import { useAppDispatch } from "../../hooks/hooks";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { IComment, ICommentFile } from "../../@type/IGetAllCommentResponse";
import FileChip from "../FileChip/FileChip";
import { getCommentFileById } from "../../api/getCommentFileById";
import {
  COMMENT_TYPES,
  FACTORY_FLAG,
  PAGE_TYPES,
  SANITIZE_HTML_OPTIONS,
} from "../../constants/constants";
import { refreshCsrData } from "../../redux/slices/csrDetailSlice";
import { toastError } from "../../utils/api";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { IPageType } from "../../@type/IPageType";
import { ICommentType } from "../../@type/ICommentType";
import useGlobalTranslate from "../../hooks/useGlobalTranslate";
import FeatureComponent from "../FeatureComponent/FeatureComponent";

const getFactoryFlagText = (value: string | null) => {
  switch (value) {
    case FACTORY_FLAG.open:
      return "logs_preview.factory_flag.chip.open";
    case FACTORY_FLAG.close:
      return "logs_preview.factory_flag.chip.close";
    default:
      return value ?? "";
  }
};

interface IProps {
  pageType: IPageType;
  iri: string;
  comments: Array<IComment> | null;
  showFactoryFlag?: boolean;
  factoryFlag?: boolean;
  linkedTocIri?: string;
  confidentialToc: boolean;
}

export default function DetailLogs(props: IProps) {
  const {
    iri,
    pageType,
    comments,
    showFactoryFlag = false,
    factoryFlag = false,
    linkedTocIri,
    confidentialToc,
  } = props;
  const dispatch = useAppDispatch();
  const { t } = useTranslation();
  const showTranslation = useGlobalTranslate(pageType === PAGE_TYPES.TOC);
  const [commentType, setCommentType] = useState<ICommentType | null>(null);

  const handleCommentPopUpClose = (isDataUpdated: boolean) => {
    if (isDataUpdated) {
      if (pageType === PAGE_TYPES.TOC) {
        dispatch(refreshTocData());
      } else if (pageType === PAGE_TYPES.CSR) {
        dispatch(refreshCsrData());
        if (commentType === COMMENT_TYPES.TOC_FROM_CSR) {
          dispatch(setToastMessage("logs_preview.flag_raised"));
        }
      }
    }
    setCommentType(null);
  };

  const handleCommentFileFetch = async (
    commentId: string,
    fileData: ICommentFile,
    showToast?: boolean
  ) => {
    const response = await getCommentFileById({
      commentId,
      fileId: fileData.id.toString(),
    });
    if (!response.data && showToast) {
      toastError(dispatch, response);
    }
    return response.data?.url ?? "";
  };

  const handleOpenFactoryFlag = () => {
    setCommentType(COMMENT_TYPES.TOC_FROM_CSR);
  };

  const handleComment = () => {
    setCommentType(
      pageType === PAGE_TYPES.TOC ? COMMENT_TYPES.TOC : COMMENT_TYPES.CSR
    );
  };

  return (
    <div className="detail_logs__wrapper">
      {commentType && (
        <CommentPopUp
          title={
            commentType === COMMENT_TYPES.TOC_FROM_CSR
              ? "logs_preview.comment.flag_title"
              : "logs_preview.heading"
          }
          iri={iri}
          linkedTocIri={linkedTocIri}
          commentType={commentType}
          isOpen
          onClose={handleCommentPopUpClose}
          factoryFlag={factoryFlag}
          showFactoryFlag={showFactoryFlag && commentType !== COMMENT_TYPES.CSR}
          disableFactoryFlag={commentType === COMMENT_TYPES.TOC_FROM_CSR}
          flipFactoryFlag={commentType === COMMENT_TYPES.TOC_FROM_CSR}
          confidentialToc={confidentialToc}
        />
      )}
      <div className="detail_logs__chat_wrapper">
        <div className="detail_logs__chat_heading_wrapper">
          <Typography
            className="cui_light_text"
            variant="h5"
            gutterBottom
            data-cy="logs-heading"
          >
            {t("logs_preview.sub_heading")}
          </Typography>
          <div className="detail_logs__action_wrapper">
            {showFactoryFlag && pageType === PAGE_TYPES.CSR && (
              <FeatureComponent
                features={["FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG"]}
              >
                <Avatar className="detail_logs__add_comment" color="primary">
                  <IconButton
                    onClick={handleOpenFactoryFlag}
                    data-cy="factory-add"
                  >
                    <FlagIcon />
                  </IconButton>
                </Avatar>
              </FeatureComponent>
            )}
            <Avatar className="detail_logs__add_comment" color="primary">
              <IconButton onClick={handleComment} data-cy="logs-add">
                <CreateIcon />
              </IconButton>
            </Avatar>
          </div>
        </div>
        {(!comments || !comments.length) && (
          <div data-cy="logs-no-content">{t("logs_preview.no_comment")}</div>
        )}
        <div className="detail_logs__chat_row_wrapper">
          {(comments ?? []).map((comment, idx) => {
            const username = `${comment.user?.firstname ?? ""} ${
              comment.user?.lastname ?? ""
            }`;
            const commentId = `${comment.id}`;
            const position = `#${comment.position ?? ""}`;

            const dataCyIdx = (comments?.length ?? 0) - idx - 1;
            return (
              <div className="detail_logs__chat_row" key={commentId}>
                <div>
                  <Avatar
                    className="detail_logs__chat_index"
                    sx={{
                      bgcolor: getColorForWord(position),
                    }}
                    data-cy={`log-comment-${dataCyIdx}-position`}
                  >
                    {position}
                  </Avatar>
                </div>
                <Paper className="detail_logs__chat_message_box" elevation={1}>
                  <div className="detail_logs__first_row">
                    <Typography
                      className="cui_bold-500"
                      variant="subtitle1"
                      gutterBottom
                      data-cy={`log-comment-${dataCyIdx}-username`}
                    >
                      {username}
                    </Typography>
                    <div className="detail_logs__chip_wrapper">
                      {comment.metadata?.confidential !== undefined && (
                        <Chip
                          color="primary"
                          size="small"
                          variant="outlined"
                          label={
                            comment.metadata.confidential
                              ? t("logs_preview.confidential.chip.open")
                              : t("logs_preview.confidential.chip.close")
                          }
                          data-cy={`log-comment-${dataCyIdx}-confidential`}
                        />
                      )}
                      {comment.discriminator && (
                        <Chip
                          color="primary"
                          size="small"
                          variant="outlined"
                          label={comment.discriminator}
                          data-cy={`log-comment-${dataCyIdx}-discriminator`}
                        />
                      )}
                      {comment.metadata?.factoryFlag && (
                        <Chip
                          color="primary"
                          size="small"
                          variant="outlined"
                          label={t(
                            getFactoryFlagText(comment.metadata.factoryFlag)
                          )}
                          data-cy={`log-comment-${dataCyIdx}-factory-flag`}
                        />
                      )}
                      {comment.public && (
                        <Chip
                          color="primary"
                          size="small"
                          variant="outlined"
                          label={t("logs_preview.external")}
                          data-cy={`log-comment-${dataCyIdx}-type`}
                        />
                      )}
                    </div>
                  </div>
                  <div>
                    <Typography
                      className="detail_logs__chat_message"
                      variant="body2"
                      gutterBottom
                      data-cy={`log-comment-${dataCyIdx}-description`}
                    >
                      <span
                        className="detail_logs__chat_content"
                        // eslint-disable-next-line react/no-danger
                        dangerouslySetInnerHTML={{
                          __html: sanitize(
                            showTranslation
                              ? comment.metadata?.translation || comment.message
                              : comment.message,
                            SANITIZE_HTML_OPTIONS
                          ),
                        }}
                      />
                    </Typography>
                  </div>
                  <div className="detail_logs__chat_files">
                    {(comment.files ?? []).map((file) => (
                      <FileChip
                        key={file.id}
                        fileName={getFileNameFromPath(file.filePath)}
                        mimeType={file.mimeType}
                        fetchSrc={(isFirstFetch) =>
                          handleCommentFileFetch(commentId, file, !isFirstFetch)
                        }
                        dataCy={`log-comment-${dataCyIdx}-file`}
                      />
                    ))}
                  </div>
                  <div className="detail_logs__chat_date">
                    <Typography
                      variant="caption"
                      gutterBottom
                      sx={{ display: "block" }}
                      data-cy={`log-comment-${dataCyIdx}-date`}
                    >
                      {formatDateTimeLong(comment.createdAt)}
                    </Typography>
                  </div>
                </Paper>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
