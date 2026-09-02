export interface IBasicApiResponse<P> {
  status?: number;
  data?: P;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  error?: any;
  errorMessage?: string;
}
