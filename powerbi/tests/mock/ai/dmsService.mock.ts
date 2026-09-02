import { jest } from "@jest/globals";

export const searchDmsMockedResult = { data: ["<div>security</div>"] };

export const summaryDmsMockedResult = {
  data: ["GPT Summary", "Mistral Summary"],
};

export const mockDmsService = () => {
  jest.unstable_mockModule("../../../src/ai/services/dmsService.ts", () => ({
    searchDms: jest.fn(() => Promise.resolve(searchDmsMockedResult)),
    summaryDms: jest.fn(() => Promise.resolve(summaryDmsMockedResult)),
  }));
};
