import request from "supertest";
import {
  getEmbedInfoMockedResult,
  mockedReportId,
  mockEmbedConfigService,
} from "../../mock/powerbi/embedConfigService.mock.ts";
import { mockAuthService } from "../../mock/common/authService.mock.ts";

mockAuthService();
mockEmbedConfigService();

const { default: app } = await import("../../../src/server.ts");

describe("Power Bi", () => {
  it("get embed token", async () => {
    const res = await request(app)
      .get(`/powerbi/getEmbedToken?reportId=${mockedReportId}`)
      .set("Authorization", `Bearer my_token`);

    expect(res.status).toBe(200);
    expect(res.body).toEqual(getEmbedInfoMockedResult);
  });
});
