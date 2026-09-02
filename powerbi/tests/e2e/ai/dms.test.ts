import request from "supertest";
import { mockAuthService } from "../../mock/common/authService.mock.ts";
import {
  mockDmsService,
  searchDmsMockedResult,
  summaryDmsMockedResult,
} from "../../mock/ai/dmsService.mock.ts";

mockAuthService();
mockDmsService();

const { default: app } = await import("../../../src/server.ts");

describe("AI - DMS", () => {
  it("Search", async () => {
    const res = await request(app)
      .get("/ai/dms/search?query=security")
      .set("Authorization", `Bearer my_token`);

    expect(res.status).toBe(200);
    expect(res.body).toEqual(searchDmsMockedResult);
  });

  it("Summary", async () => {
    const res = await request(app)
      .get("/ai/dms/summary?id=1141")
      .set("Authorization", `Bearer my_token`);

    expect(res.status).toBe(200);
    expect(res.body).toEqual(summaryDmsMockedResult);
  });
});
