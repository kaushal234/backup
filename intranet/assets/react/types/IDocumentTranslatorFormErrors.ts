import { IDocumentTranslatorFormData } from "./IDocumentTranslatorFormData";

export type IDocumentTranslatorFormErrors = {
  [K in keyof IDocumentTranslatorFormData]?: string;
};
