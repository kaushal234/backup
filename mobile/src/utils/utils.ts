import he from "he";
import sanitizeHtml, { IOptions } from "sanitize-html";
import { ICsrSurveyFormData } from "../@type/ICsrSurveyFormData";
import { IDropdownItem } from "../@type/IDropdownItem";
import { IFeatureAuthorisedParams } from "../@type/IFeatureAuthorisedParams";
import { IFileDownloadParams } from "../@type/IFileDownloadParams";
import { IFilePreview } from "../@type/IFilePreview";
import { IFormatContact } from "../@type/IFormatContact";
import { IComment } from "../@type/IGetAllCommentResponse";
import { IGetAllCustomerServiceRecordFilterRawValues } from "../@type/IGetAllCustomerServiceRecordFilterRawValues";
import { IGetAllCustomerServiceRecordFilters } from "../@type/IGetAllCustomerServiceRecordFilters";
import { IGetAllEquipmentRecordFilterRawValues } from "../@type/IGetAllEquipmentRecordFilterRawValues";
import { IGetAllEquipmentRecordFilters } from "../@type/IGetAllEquipmentRecordFilters";
import { IGetAllTechnicalOnCallsFilterRawValues } from "../@type/IGetAllTechnicalOnCallsFilterRawValues";
import { IGetAllTechnicalOnCallsFilters } from "../@type/IGetAllTechnicalOnCallsFilters";
import { IAuditLogProperty } from "../@type/IGetAuditLogResponse";
import {
  IProductDemeritClaimLegacy,
  ITechnicianOnCallLinks,
  IWarrantyClaimLegacy,
} from "../@type/IGetTechnicalOnCallsResponse";
import { IHighlightParts } from "../@type/IHighlightParts";
import { ISurveyResponse } from "../@type/ISurveyResponse";
import { ITime } from "../@type/ITime";
import { ITocLink } from "../@type/ITocLink";
import { AVATAR_BG_COLORS, FETCH_FILE_TYPES } from "../constants/constants";
import { idbResetDatabase } from "../idb";
import { getSecondsFromDays } from "./date";

export const extractDigits = (input: string): string => {
  return input.replace(/\D/g, "");
};

export const extractFloat = (input: string): string => {
  let foundDecimal = false;
  return input
    .split("")
    .filter((char) => {
      if (/\d/.test(char)) return true;
      if (char === "." && !foundDecimal) {
        foundDecimal = true;
        return true;
      }
      return false;
    })
    .join("");
};

export const generateRandomNumber = () => {
  const min = 1;
  const max = 10 ** 7;
  return Math.floor(Math.random() * (max - min + 1)) + min;
};

export const getHighlightParts = (
  value: string,
  matchString: string
): Array<IHighlightParts> => {
  if (!matchString) {
    return [{ text: value, highlight: false }];
  }
  const parts: IHighlightParts[] = [];
  const regex = new RegExp(`(${matchString})`, "gi");
  let match;
  let lastIndex = 0;

  // eslint-disable-next-line no-cond-assign
  while ((match = regex.exec(value)) !== null) {
    if (match.index > lastIndex) {
      parts.push({
        text: value.substring(lastIndex, match.index),
        highlight: false,
      });
    }
    parts.push({
      text: match[0],
      highlight: true,
    });
    lastIndex = regex.lastIndex;
  }

  if (lastIndex < value.length) {
    parts.push({
      text: value.substring(lastIndex),
      highlight: false,
    });
  }

  return parts;
};

const getIdsFromDropdownList = (list: Array<IDropdownItem>) => {
  return list.map((item) => item.id);
};

export const formatTocFilters = (
  rawValues: IGetAllTechnicalOnCallsFilterRawValues
): IGetAllTechnicalOnCallsFilters => {
  const result: IGetAllTechnicalOnCallsFilters = {};
  if (rawValues.serialNumber) {
    result.equipmentRecord = rawValues.serialNumber.id;
  }
  if (rawValues.status.length) {
    result.status = getIdsFromDropdownList(rawValues.status);
  }
  if (rawValues.assignee) {
    result.assignee = getIdsFromDropdownList(rawValues.assignee);
  }
  if (rawValues.unitOperationalStatus.length) {
    result.unitOperationalStatus = getIdsFromDropdownList(
      rawValues.unitOperationalStatus
    );
  }
  if (rawValues.technicianOnCallType.length) {
    result.technicianOnCallType = getIdsFromDropdownList(
      rawValues.technicianOnCallType
    );
  }
  if (rawValues.serviceActivity.length) {
    result.serviceActivity = getIdsFromDropdownList(rawValues.serviceActivity);
  }
  if (rawValues.indiceFactor.length) {
    result.indiceFactor = getIdsFromDropdownList(rawValues.indiceFactor);
  }
  if (rawValues.tags.length) {
    result.tags = getIdsFromDropdownList(rawValues.tags);
  }
  if (rawValues.createdBy) {
    result.createdBy = getIdsFromDropdownList(rawValues.createdBy);
  }
  if (rawValues.createdAfter) {
    result["createdAt[after]"] = rawValues.createdAfter;
  }
  if (rawValues.createdBefore) {
    result["createdAt[before]"] = rawValues.createdBefore;
  }
  if (rawValues.solvedAfter) {
    result["solvedAt[after]"] = rawValues.solvedAfter;
  }
  if (rawValues.solvedBefore) {
    result["solvedAt[before]"] = rawValues.solvedBefore;
  }
  if (rawValues.salesOrganisation) {
    result["equipmentRecord.salesOrganisation"] = getIdsFromDropdownList(
      rawValues.salesOrganisation
    );
  }
  if (rawValues.serviceOrganisation) {
    result.salesOrganisationService = getIdsFromDropdownList(
      rawValues.serviceOrganisation
    );
  }
  if (rawValues.manufacturerLocation) {
    result["equipmentRecord.manufacturerLocation"] = getIdsFromDropdownList(
      rawValues.manufacturerLocation
    );
  }
  if (rawValues.equipmentType) {
    result["equipmentRecord.product.family.productType"] =
      getIdsFromDropdownList(rawValues.equipmentType);
  }
  if (rawValues.model) {
    result["equipmentRecord.product"] = getIdsFromDropdownList(rawValues.model);
  }
  if (rawValues.airport) {
    result.airport = getIdsFromDropdownList(rawValues.airport);
  }
  if (rawValues.late !== null) {
    result.late = rawValues.late.id;
  }
  if (rawValues.factoryFlag !== null) {
    result.factoryFlag = rawValues.factoryFlag.id;
  }
  if (rawValues.factoryFlagRecentlyClosed !== null) {
    result.factoryFlagRecentlyClosed = rawValues.factoryFlagRecentlyClosed.id;
  }
  if (rawValues.survey !== null) {
    result.surveyAnswerThisMonth = rawValues.survey.id;
  }
  if (rawValues.buyer) {
    result["equipmentRecord.buyer"] = getIdsFromDropdownList(rawValues.buyer);
  }
  if (rawValues.country) {
    result["airport.country"] = getIdsFromDropdownList(rawValues.country);
  }
  if (rawValues.tocPart) {
    result["parts.partNumber"] = rawValues.tocPart;
  }
  if (rawValues.endUser) {
    result["equipmentRecord.endUser"] = getIdsFromDropdownList(
      rawValues.endUser
    );
  }
  if (rawValues.sprPart) {
    result["sparePartsRequests.parts.partNumber"] = rawValues.sprPart;
  }
  if (rawValues.maintainer) {
    result["equipmentRecord.maintainer"] = getIdsFromDropdownList(
      rawValues.maintainer
    );
  }
  if (rawValues.confidential !== null) {
    result.confidential = rawValues.confidential.id;
  }
  if (rawValues.title) {
    result.title = rawValues.title;
  }
  if (rawValues.technician) {
    result.technician = getIdsFromDropdownList(rawValues.technician);
  }
  if (rawValues.errorCodes) {
    result.errorCodes = rawValues.errorCodes;
  }
  if (rawValues.assigneeOrTechnician) {
    result.actor = getIdsFromDropdownList(rawValues.assigneeOrTechnician);
  }

  return result;
};

export const formatErFilters = (
  rawValues: IGetAllEquipmentRecordFilterRawValues
): IGetAllEquipmentRecordFilters => {
  const result: IGetAllEquipmentRecordFilters = {};
  if (rawValues.serialNumber) {
    result.serialNumber = rawValues.serialNumber.id;
  }
  if (rawValues.equipmentType) {
    result["product.family.productType"] = getIdsFromDropdownList(
      rawValues.equipmentType
    );
  }
  if (rawValues.model) {
    result.product = getIdsFromDropdownList(rawValues.model);
  }
  if (rawValues.airport) {
    result.airport = rawValues.airport.id;
  }
  if (rawValues.buyer) {
    result.buyer = rawValues.buyer.id;
  }
  if (rawValues.endUser) {
    result.endUser = rawValues.endUser.id;
  }
  if (rawValues.maintainer) {
    result.maintainer = rawValues.maintainer.id;
  }
  return result;
};

export const formatCsrFilters = (
  rawValues: IGetAllCustomerServiceRecordFilterRawValues
): IGetAllCustomerServiceRecordFilters => {
  const result: IGetAllCustomerServiceRecordFilters = {};
  if (rawValues.serialNumber) {
    result.equipmentRecord = rawValues.serialNumber.id;
  }
  if (rawValues.status.length) {
    result.status = getIdsFromDropdownList(rawValues.status);
  }
  if (rawValues.createdBy) {
    result.createdBy = rawValues.createdBy.id;
  }
  if (rawValues.createdAfter) {
    result["createdAt[after]"] = rawValues.createdAfter;
  }
  if (rawValues.createdBefore) {
    result["createdAt[before]"] = rawValues.createdBefore;
  }
  if (rawValues.salesOrganisation) {
    result["equipmentRecord.salesOrganisation"] =
      rawValues.salesOrganisation.id;
  }
  if (rawValues.serviceOrganisation) {
    result["equipmentRecord.salesOrganisationService"] =
      rawValues.serviceOrganisation.id;
  }
  if (rawValues.manufacturerLocation) {
    result["equipmentRecord.manufacturerLocation"] =
      rawValues.manufacturerLocation.id;
  }
  if (rawValues.equipmentType) {
    result["equipmentRecord.product.family.productType"] =
      getIdsFromDropdownList(rawValues.equipmentType);
  }
  if (rawValues.model) {
    result["equipmentRecord.product"] = getIdsFromDropdownList(rawValues.model);
  }
  if (rawValues.airport) {
    result.airport = rawValues.airport.id;
  }
  if (rawValues.serviceTechnician) {
    result["interventions.leader"] = rawValues.serviceTechnician.id;
  }
  if (rawValues.endUser) {
    result["equipmentRecord.endUser"] = rawValues.endUser.id;
  }
  if (rawValues.completedAfter) {
    result["completedAt[after]"] = rawValues.completedAfter;
  }
  if (rawValues.completedBefore) {
    result["completedAt[before]"] = rawValues.completedBefore;
  }
  if (rawValues.closedAfter) {
    result["closedAt[after]"] = rawValues.closedAfter;
  }
  if (rawValues.closedBefore) {
    result["closedAt[before]"] = rawValues.closedBefore;
  }
  if (rawValues.country) {
    result["airport.country"] = getIdsFromDropdownList(rawValues.country);
  }
  if (rawValues.discriminator) {
    result.discriminator = getIdsFromDropdownList(rawValues.discriminator);
  }
  return result;
};

export const downloadFile = (data: IFileDownloadParams) => {
  if (!data.url) return;
  const link = document.createElement("a");
  link.href = data.url;
  link.download = data.fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const hashStringToIndex = (str: string, arrayLength: number) => {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    // eslint-disable-next-line no-bitwise
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  return Math.abs(hash) % arrayLength;
};

export const getColorForWord = (word: string) => {
  const index = hashStringToIndex(word, AVATAR_BG_COLORS.length);
  return AVATAR_BG_COLORS[index];
};

export const getInitials = (fullName: string): string => {
  const nameParts = fullName.trim().split(/\s+/);
  const firstInitial = nameParts[0].charAt(0).toUpperCase();
  const secondInitial =
    nameParts.length > 1 ? nameParts[1].charAt(0).toUpperCase() : "";
  return `${firstInitial}${secondInitial}`;
};

export const getFileNameFromPath = (value: string) => {
  const match = value.match(/\d{14}-(.*)$/);
  const filename = match?.[1] ?? "";
  return filename || value;
};

export const mapFileListToFileArray = (fileList: FileList | null) => {
  if (!fileList || !fileList.length) return null;
  const result: Array<File> = [];
  for (let i = 0; i < fileList.length; i++) {
    const file = fileList[i];
    result.push(file);
  }
  return result;
};

export const convertToTitleCase = (input: string): string => {
  return input
    .toLowerCase()
    .split(" ")
    .map((word) =>
      word
        .split("-")
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join("-")
    )
    .join(" ");
};

export const convertCommentsToFilePreviews = (
  comments: Array<IComment>,
  chipText: string
) => {
  const result: Array<IFilePreview> = [];
  comments.forEach((comment) => {
    if (comment.files.length) {
      const file = comment.files[0];
      result.push({
        name: file.filePath,
        mimeType: file.mimeType,
        additionalInfo: {
          dataTypeId: comment.id?.toString() ?? "",
          fileId: file.id.toString(),
          type: FETCH_FILE_TYPES.CommentFile,
        },
        createdAt: file.createdAt,
        label: `${chipText} (#${comment.position})`,
        chipText: `${chipText} (#${comment.position})`,
        description: comment.files[0].description,
      });
    }
  });
  result.sort(
    (a, b) =>
      new Date(b.createdAt ?? "").getTime() -
      new Date(a.createdAt ?? "").getTime()
  );
  return result;
};

export const generateRandomString = () => {
  const number = generateRandomNumber();
  const date = new Date().toISOString();
  return `${number}_${date}`;
};

const resetLocalStorage = () => {
  localStorage.clear();
};

const resetSessionStorage = () => {
  sessionStorage.clear();
};

const resetCookies = () => {
  document.cookie.split(";").forEach((cookie) => {
    const eqPos = cookie.indexOf("=");
    const name = eqPos > -1 ? cookie.substring(0, eqPos) : cookie;
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT`;
  });
};

const resetIndexDB = async () => {
  return idbResetDatabase();
};

const resetServiceWorker = async () => {
  const registrations = await navigator.serviceWorker.getRegistrations();
  return registrations.map((registration) => registration.unregister());
};

const resetCache = async () => {
  const allCaches = await caches.keys();
  return allCaches.map((cache) => caches.delete(cache));
};

export const resetWebsite = async () => {
  const swPromise = resetServiceWorker();
  const idbPromise = resetIndexDB();
  const cachePromise = resetCache();
  resetLocalStorage();
  resetSessionStorage();
  resetCookies();
  await Promise.all([idbPromise, swPromise, cachePromise]);
};

export const calculateTimeFromSeconds = (seconds: number): ITime => {
  let total = seconds;
  const dayInSeconds = getSecondsFromDays(1);
  const days = Math.floor(total / dayInSeconds);
  total %= dayInSeconds;
  const hours = Math.floor(total / 3600);
  total %= 3600;
  const minutes = Math.ceil(total / 60);
  return {
    days,
    hours,
    minutes,
  };
};

export const formatTime = (seconds: number) => {
  const time = calculateTimeFromSeconds(seconds);
  if (time.days) {
    return `${time.days}d ${time.hours}h`;
  }
  return `${time.hours}h ${time.minutes}m`;
};

export const formatFactoryTime = (data: Array<IAuditLogProperty>) => {
  const factoryFlagOpen = data.find((item) => item.value === "1");
  return formatTime(factoryFlagOpen?.time ?? 0);
};

export const formatNmcTime = (data: Array<IAuditLogProperty>) => {
  const nmc = data.find((item) => item.value === "NMC");
  return formatTime(nmc?.time ?? 0);
};

export const getIdFromIri = (value: string | null | undefined) => {
  if (!value) return "";
  const parts = value.split("/");
  return parts[parts.length - 1];
};

export const formatContact = (contact: IFormatContact): string => {
  const order = ["mobile", "mobile_alternate", "phone"];
  const texts: Array<string> = [];
  order.forEach((type) => {
    const items = contact.phones
      .filter((phone) => phone.type === type)
      .map((phone) => phone.number);
    texts.push(...items);
  });
  texts.push(contact.email);
  return `${contact.firstname} ${contact.lastname} - ${texts[0]}`;
};

export const toggleString = (list: Array<string>, str: string) => {
  return list.includes(str)
    ? list.filter((item) => item !== str)
    : [...list, str];
};

export const formatSurveyFormData = (
  data: ICsrSurveyFormData
): Array<ISurveyResponse> => {
  return [
    {
      ...data.oldData?.[0],
      questionSurveyCustomerServiceRecord:
        "/service/question_survey_customer_service_records/1",
      answer: data.data.question1.value,
      comment: data.data.question1.comment,
    },
    {
      ...data.oldData?.[1],
      questionSurveyCustomerServiceRecord:
        "/service/question_survey_customer_service_records/2",
      answer: data.data.question2.value,
      comment: data.data.question2.comment,
    },
    {
      ...data.oldData?.[2],
      questionSurveyCustomerServiceRecord:
        "/service/question_survey_customer_service_records/3",
      answer: data.data.question3.value,
      comment: data.data.question3.comment,
    },
    {
      ...data.oldData?.[3],
      questionSurveyCustomerServiceRecord:
        "/service/question_survey_customer_service_records/4",
      answer: data.data.question4.value.join(","),
      comment: data.data.question4.comment,
    },
    {
      ...data.oldData?.[4],
      questionSurveyCustomerServiceRecord:
        "/service/question_survey_customer_service_records/5",
      answer: data.data.question5.value ? "1" : "0",
      comment: data.data.question5.comment,
    },
  ];
};

export const isFeatureAuthorised = (data: IFeatureAuthorisedParams) => {
  const { userFeatures, features, requiresAll } = data;
  let isUserAuthorised = false;
  if (requiresAll) {
    isUserAuthorised = features.every((feature) =>
      userFeatures.includes(feature)
    );
  } else {
    isUserAuthorised = features.some((feature) =>
      userFeatures.includes(feature)
    );
  }
  return isUserAuthorised;
};

export const extractContactId = (iri: string) => {
  return iri.replace("/sales/extranet_users/", "");
};

export const handleDropdownOpen = () => {
  document.documentElement.style.overflow = "hidden";
};

export const handleDropdownClose = () => {
  document.documentElement.style.overflow = "scroll";
};

export const restrictFilename = (filename: string): string => {
  const lastDotIndex = filename.lastIndexOf(".");
  const base = lastDotIndex === -1 ? filename : filename.slice(0, lastDotIndex);
  return base.replace(/[^A-Za-z0-9_\- ]/g, "");
};

export const cleanFilename = (filename: string): string => {
  const filenameWithSpaces = restrictFilename(filename);
  return filenameWithSpaces.trim().replace(/\s+/g, "-");
};

export const extractLocationId = (value?: string) => {
  return (value ?? "").replace("/locations/", "");
};

export const formatTocLinks = (data: ITechnicianOnCallLinks | undefined) => {
  const result: Array<ITocLink> = [];
  Object.keys(data ?? {}).forEach((key) => {
    switch (key) {
      case "WC":
        (data?.[key] ?? []).forEach((item: IWarrantyClaimLegacy) => {
          result.push({
            id: null,
            module: "WC",
            ref: `${item.id ?? ""}`,
            description: item.description ?? "",
            status: convertToTitleCase(item.status ?? ""),
          });
        });
        break;
      case "PDC":
        (data?.[key] ?? []).forEach((item: IProductDemeritClaimLegacy) => {
          result.push({
            id: null,
            module: "PDC",
            ref: `${item.id ?? ""}`,
            description: item.shortDescription ?? "",
            status: convertToTitleCase(item.status ?? ""),
          });
        });
        break;
      default:
        break;
    }
  });
  return result;
};

export const sanitize = (value?: string | null, options?: IOptions) => {
  const decoded = he.decode(value || "");
  return sanitizeHtml(decoded, options);
};

export const extractText = (value: string) => {
  return new DOMParser().parseFromString(value, "text/html").body.textContent;
};
