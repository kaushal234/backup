import { jest } from "@jest/globals";

export const mockedReportId = "ebb5efd6-fe4e-493c-8086-21bfcf81787c";

export const getEmbedInfoMockedResult = {
  accessToken: "my_access_token",
  embedUrl: [
    {
      reportId: mockedReportId,
      reportName: "my_report_name",
      embedUrl: "my_embed_url",
    },
  ],
  expiry: "2025-01-01T00:00:00Z",
  status: 200,
};

export const mockEmbedConfigService = () => {
  jest.unstable_mockModule(
    "../../../src/powerbi/services/embedConfigService.ts",
    () => ({
      getEmbedInfo: jest.fn(() => Promise.resolve(getEmbedInfoMockedResult)),
    })
  );
};
