import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { IComment } from "../../@type/IGetAllCommentResponse";
import { ICustomerServiceRecord } from "../../@type/IGetCustomerServiceRecordResponse";
import { IFilePreview } from "../../@type/IFilePreview";
import DetailFiles from "../DetailFiles/DetailFiles";
import { getCommentFileById } from "../../api/getCommentFileById";
import { convertCommentsToFilePreviews } from "../../utils/utils";
import { FETCH_FILE_TYPES, IDB_DATABASE } from "../../constants/constants";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { postCsrFileByCsrId } from "../../api/postCsrFileByCsrId";
import { refreshCsrData } from "../../redux/slices/csrDetailSlice";
import { getCustomerServiceRecordFileById } from "../../api/getCustomerServiceRecordFileById";
import { IFileUploadPopUpSubmitParams } from "../../@type/IFileUploadPopUpSubmitParams";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { setToastMessage } from "../../redux/slices/toastSlice";

interface IProps {
  data: ICustomerServiceRecord;
  comments: Array<IComment> | null;
}

const convertCsrFilesToFilePreviews = (data: ICustomerServiceRecord) => {
  const result: Array<IFilePreview> = [];
  data.files?.forEach((file) => {
    result.push({
      name: file.filePath,
      mimeType: file.mimeType,
      createdAt: file.createdAt,
      additionalInfo: {
        type: FETCH_FILE_TYPES.CsrFile,
        dataTypeId: data.id?.toString() ?? "",
        fileId: file.id.toString(),
      },
      description: file.description,
    });
  });
  return result;
};

export default function CsrDetailFiles(props: IProps) {
  const { data, comments } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const [files, setFiles] = useState<Array<IFilePreview>>([]);

  const makeFilePreviews = () => {
    const result: Array<IFilePreview> = [];
    result.push(
      ...convertCommentsToFilePreviews(
        comments ?? [],
        t("files_preview.comment_file")
      )
    );
    result.push(...convertCsrFilesToFilePreviews(data));
    result.sort(
      (a, b) =>
        new Date(b.createdAt ?? "").getTime() -
        new Date(a.createdAt ?? "").getTime()
    );
    setFiles(result);
  };

  const handleFetchUrl = async (index: number) => {
    if (files[index]) {
      const file = files[index];
      let url = "";
      if (file.additionalInfo?.type === FETCH_FILE_TYPES.CommentFile) {
        const response = await getCommentFileById({
          commentId: file.additionalInfo.dataTypeId,
          fileId: file.additionalInfo.fileId,
        });
        url = response.data?.url ?? "";
      } else if (file.additionalInfo?.type === FETCH_FILE_TYPES.CsrFile) {
        const response = await getCustomerServiceRecordFileById({
          csrId: file.additionalInfo.dataTypeId,
          fileId: file.additionalInfo.fileId,
        });
        url = response.data?.url ?? "";
      }
      const newFile: IFilePreview = { ...file, url };
      const newFiles = [...files];
      newFiles[index] = newFile;
      setFiles(newFiles);
      return url;
    }
    return Promise.resolve("");
  };

  const handleFileUpload = async (
    newFileData: IFileUploadPopUpSubmitParams
  ) => {
    dispatch(showMainLoader(true));
    const filePayloads = newFileData.files.map((fileData) => ({
      csrId: data.id?.toString() ?? "",
      file: fileData.file,
      description: fileData.description,
    }));
    if (isOnline) {
      const promises = filePayloads.map((payload) =>
        postCsrFileByCsrId(payload)
      );
      await Promise.all(promises);
      dispatch(refreshCsrData());
    } else {
      const promises = filePayloads.map((payload) =>
        registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_post_csr_file,
          payload,
        })
      );
      await Promise.all(promises);
      dispatch(setToastMessage("files_preview.offline"));
      dispatch(showMainLoader(false));
    }
  };

  useEffect(() => {
    makeFilePreviews();
  }, [data, comments]);

  return (
    <DetailFiles
      files={files}
      fetchUrl={handleFetchUrl}
      onFileUpload={handleFileUpload}
      hasMainFile
    />
  );
}
