export interface IApiResponse<T> {
  status: number;
  data?: T;
  message?: string;
}
