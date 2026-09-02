import request from "supertest";
import {
  mockTocService,
  searchTocMockedResult,
} from "../../mock/ai/tocService.mock.ts";
import { mockAuthService } from "../../mock/common/authService.mock.ts";

mockAuthService();
mockTocService();

const { default: app } = await import("../../../src/server.ts");

describe("TOC Ai Search", () => {
  it("Search", async () => {
    const res = await request(app)
      .get("/ai/toc/search?query=green")
      .set("Authorization", `Bearer my_token`);

    expect(res.status).toBe(200);
    expect(res.body).toEqual(searchTocMockedResult);
  });
});
