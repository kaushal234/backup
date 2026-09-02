import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import { IComment } from "../../@type/IGetAllCommentResponse";
import { refreshTocData } from "../../redux/slices/tocDetailSlice";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import DetailFiles from "../DetailFiles/DetailFiles";
import { IFilePreview } from "../../@type/IFilePreview";
import { convertCommentsToFilePreviews } from "../../utils/utils";
import { FETCH_FILE_TYPES, IDB_DATABASE } from "../../constants/constants";
import { getTechnicalOnCallMainFileById } from "../../api/getTechnicalOnCallMainFileById";
import { getTechnicalOnCallFileById } from "../../api/getTechnicalOnCallFileById";
import { getCommentFileById } from "../../api/getCommentFileById";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IFileUploadPopUpSubmitParams } from "../../@type/IFileUploadPopUpSubmitParams";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { setToastMessage } from "../../redux/slices/toastSlice";
import {
  IPostTechnicianOnCallFilesApiPayload,
  postTechnicianOnCallFiles,
} from "../../api/postTechnicianOnCallFiles";
import { IUploadFile } from "../../@type/IFile";
import {
  deleteTechnicianOnCallFile,
  IDeleteTechnicianOnCallFileApiPayload,
} from "../../api/deleteTechnicianOnCallFile";

interface IProps {
  data: ITechnicianOnCall;
  comments: Array<IComment> | null;
}

const convertTocFilesToFilePreviews = (data: ITechnicianOnCall) => {
  const result: Array<IFilePreview> = [];
  data.files?.forEach((file) => {
    result.push({
      name: file.filePath,
      mimeType: file.mimeType,
      createdAt: file.createdAt,
      additionalInfo: {
        type: FETCH_FILE_TYPES.TocFile,
        dataTypeId: data.id?.toString() ?? "",
        fileId: file.id.toString(),
      },
      description: file.description,
    });
  });
  return result;
};

const convertTocMainFileToFilePreview = (
  data: ITechnicianOnCall
): IFilePreview | null => {
  if (data.mainFile) {
    return {
      name: data.mainFile.filePath,
      mimeType: data.mainFile.mimeType,
      label: "toc_details.files.main_file",
      chipText: "toc_details.files.main_file",
      additionalInfo: {
        type: FETCH_FILE_TYPES.TocMainFile,
        dataTypeId: data.id?.toString() ?? "",
        fileId: data.mainFile.id.toString(),
      },
      description: data.mainFile.description,
    };
  }
  return null;
};

export default function TocDetailFiles(props: IProps) {
  const { data, comments } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const [files, setFiles] = useState<Array<IFilePreview>>([]);
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const makeFilePreviews = async () => {
    let result: Array<IFilePreview> = [];
    result.push(
      ...convertCommentsToFilePreviews(
        comments ?? [],
        t("files_preview.comment_file")
      )
    );
    result.push(...convertTocFilesToFilePreviews(data));
    result.sort(
      (a, b) =>
        new Date(b.createdAt ?? "").getTime() -
        new Date(a.createdAt ?? "").getTime()
    );
    const mainFilePreview = convertTocMainFileToFilePreview(data);
    if (mainFilePreview) {
      result = [mainFilePreview, ...result];
    }
    setFiles(result);
  };

  const handleFetchUrl = async (index: number) => {
    if (files[index]) {
      const file = files[index];
      let url = "";
      if (file.additionalInfo?.type === FETCH_FILE_TYPES.TocMainFile) {
        const response = await getTechnicalOnCallMainFileById({
          tocId: file.additionalInfo.dataTypeId,
          fileId: file.additionalInfo.fileId,
        });
        url = response.data?.url ?? "";
      } else if (file.additionalInfo?.type === FETCH_FILE_TYPES.TocFile) {
        const response = await getTechnicalOnCallFileById({
          tocId: file.additionalInfo.dataTypeId,
          fileId: file.additionalInfo.fileId,
        });
        url = response.data?.url ?? "";
      } else if (file.additionalInfo?.type === FETCH_FILE_TYPES.CommentFile) {
        const response = await getCommentFileById({
          commentId: file.additionalInfo.dataTypeId,
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
    const otherFiles: Array<IUploadFile> = newFileData.files.map(
      (fileData) => ({
        file: fileData.file,
        description: fileData.description,
      })
    );
    const mainFile: IUploadFile | null = newFileData.mainFile
      ? {
          description: newFileData.mainFile?.description,
          file: newFileData.mainFile?.file,
        }
      : null;

    const params: IPostTechnicianOnCallFilesApiPayload = {
      tocId: data.id?.toString() ?? "",
      files: otherFiles,
      mainFile,
    };
    if (isOnline) {
      await postTechnicianOnCallFiles(params);
      dispatch(refreshTocData());
    } else {
      registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_post_toc_files,
        payload: params,
      });
      dispatch(setToastMessage("files_preview.offline"));
      dispatch(showMainLoader(false));
    }
  };

  const handleDelete = async (file: IFilePreview) => {
    dispatch(showMainLoader(true));
    const params: IDeleteTechnicianOnCallFileApiPayload = {
      tocId: data.id?.toString() ?? "",
      fileId: file.additionalInfo?.fileId ?? "",
    };
    if (isOnline) {
      await deleteTechnicianOnCallFile(params);
      dispatch(refreshTocData());
    } else {
      registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_delete_toc_file,
        payload: params,
      });
      dispatch(setToastMessage("files_preview.delete"));
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
      hasMainFile={!!data.mainFile}
      onDelete={handleDelete}
    />
  );
}
