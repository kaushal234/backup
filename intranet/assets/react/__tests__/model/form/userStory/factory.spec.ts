import { describe, it } from "mocha";
import { expect } from "chai";
import { userStoryFactory } from "../../../../model/form/user_story/factory";

const valuesUserStory = {
  checkBoxEmail: true,
  checkBoxNotification: true,
  description: "user story description",
  category: { value: "create" },
  specification: "/mis/specifications/1",
  peopleProperties: [
    {
      peopleProperty: "creator",
    },
  ],
  roleAccesses: [
    {
      group: {
        value: "ROLE_GSE",
      },
      locationProperty: "SOR",
    },
  ],
  objectEmail: "object email",
  bodyEmail: "body email",
  emailFollower: true,
  notification: "notification message",
  notificationFollower: true,
  follower: true,
};

describe("User Story factory", () => {
  it("should return a user story with empty roleToNotify and empty email (recipient and copy, property)", () => {
    const modifiedValues = {
      ...valuesUserStory,
      roleRecipientNotify: [],
      roleRecipientEmail: [],
      roleCopyEmail: [],
      peoplePropertyEmail: [],
    };

    const result = userStoryFactory(modifiedValues, 1);

    expect(result).to.deep.equal({
      specification: "/mis/specifications/1",
      checkBoxEmail: true,
      checkBoxNotification: true,
      category: { value: "create" },
      description: "user story description",
      peopleProperties: ["creator"],
      roleAccesses: [
        {
          group: "ROLE_GSE",
          locationProperty: "SOR",
        },
      ],
      emails: [
        {
          object: "object email",
          body: "body email",
          follower: true,
          peopleProperties: [],
          recipientAccesses: [],
          copyAccesses: [],
        },
      ],
      notifications: [
        {
          message: "notification message",
          follower: true,
          roleToNotify: [],
        },
      ],
    });
  });

  it("should return a user story with populated roleToNotify and email(recipient, copy and property)", () => {
    const modifiedValues = {
      ...valuesUserStory,
      roleRecipientNotify: [
        {
          group: {
            value: "ROLE_GSE",
          },
          locationProperty: "SOR",
        },
      ],
      roleRecipientEmail: [
        {
          group: {
            value: "ROLE_GSE",
          },
          locationProperty: "SOR",
        },
      ],
      roleCopyEmail: [
        {
          group: {
            value: "ROLE_GSE",
          },
          locationProperty: "SOR",
        },
      ],
      peoplePropertyEmail: [
        {
          peopleProperty: "master",
        },
      ],
    };

    const result = userStoryFactory(modifiedValues, 1);

    expect(result).to.deep.equal({
      specification: "/mis/specifications/1",
      checkBoxEmail: true,
      checkBoxNotification: true,
      category: { value: "create" },
      description: "user story description",
      peopleProperties: ["creator"],
      roleAccesses: [
        {
          group: "ROLE_GSE",
          locationProperty: "SOR",
        },
      ],
      emails: [
        {
          object: "object email",
          body: "body email",
          follower: true,
          peopleProperties: ["master"],
          recipientAccesses: [
            {
              group: "ROLE_GSE",
              locationProperty: "SOR",
            },
          ],
          copyAccesses: [
            {
              group: "ROLE_GSE",
              locationProperty: "SOR",
            },
          ],
        },
      ],
      notifications: [
        {
          message: "notification message",
          follower: true,
          roleToNotify: [
            {
              group: "ROLE_GSE",
              locationProperty: "SOR",
            },
          ],
        },
      ],
    });
  });
});
