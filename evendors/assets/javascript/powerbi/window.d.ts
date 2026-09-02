declare global {
  interface Window {
    user?: {
      token?: string;
    };
    APP_CONFIG?: {
      INTERNAL_API_ENDPOINT?: string;
      POWERBI_URL?: string;
      POWER_BI_REPORT_64?: string;
    };
    'powerbi-client'?: any;
  }
}

export {};
