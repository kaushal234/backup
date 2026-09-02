export interface IDocumentTranslation {
  "@context"?: string;
  "@id": string;
  "@type": string;
  documentId?: string | null;
  documentKey?: string | null;
  estimatedSeconds: number | null;
  targetLang: string;
  formality: string;
  filename: string;
  mimeType?: string;
  size: number | null;
  status: string;
  user?: string;
  createdAt: string | null;
  updatedAt?: string | null;
  errorMessage: string | null;
  id: number | null;
}
