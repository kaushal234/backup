import { jest } from "@jest/globals";

export const searchTocMockedResult = { data: ["<div>green</div>"] };

export const mockTocService = () => {
  jest.unstable_mockModule("../../../src/ai/services/tocService.ts", () => ({
    searchToc: jest.fn(() => Promise.resolve(searchTocMockedResult)),
  }));
};
