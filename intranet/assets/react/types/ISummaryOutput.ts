export interface ISummaryOutput {
  "@context": SummaryOutputContext;
  "@id": string;
  "@type": string;
  logIri: string | null;
  summary: string;
}

interface SummaryOutputContext {
  "@vocab": string;
  hydra: string;
  logIri: string;
  summary: string;
}
