export interface IFile {
  "@id": string;
  "@type": string;
  id: number;
  filePath: string;
  createdAt: string;
  mimeType: string;
  description: string | null;
}
