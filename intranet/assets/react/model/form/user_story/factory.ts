import _ from "lodash";

const userStoryFactory = (values: any, specificationId?: any) => {
  let peopleProperties = [];
  if (values.peopleProperties !== undefined) {
    peopleProperties = values.peopleProperties.map(
      (peopleProperty: any) => peopleProperty.peopleProperty
    );
  }

  let roleAccesses = [];
  if (values.roleAccesses !== undefined) {
    roleAccesses = values.roleAccesses.map((roleAccess: any) => ({
      group: roleAccess.group.value,
      locationProperty: roleAccess.locationProperty
        ? roleAccess.locationProperty
        : "",
    }));
  }

  let emails: Array<any>;
  const { checkBoxEmail } = values;
  if (values.checkBoxEmail) {
    emails = [
      {
        object: values.objectEmail,
        body: values.bodyEmail,
        follower: values.emailFollower,
        peopleProperties: (values.peoplePropertyEmail || []).map(
          (peopleProperty: any) => peopleProperty.peopleProperty
        ),
        recipientAccesses: (values.roleRecipientEmail || []).map(
          (roleAccess: any) => ({
            group: roleAccess.group.value,
            locationProperty: roleAccess.locationProperty,
          })
        ),
        copyAccesses: (values.roleCopyEmail || []).map((roleAccess: any) => ({
          group: roleAccess.group.value,
          locationProperty: roleAccess.locationProperty,
        })),
      },
    ];
  } else {
    emails = [];
  }

  let notifications: Array<any> = [];
  const { checkBoxNotification } = values;
  if (values.checkBoxNotification) {
    notifications = [
      {
        message: values.notification,
        follower: values.notificationFollower,
        roleToNotify: (values.roleRecipientNotify || []).map(
          (roleAccess: any) => ({
            group: roleAccess.group.value,
            locationProperty: roleAccess.locationProperty,
          })
        ),
      },
    ];
  } else {
    notifications = [];
  }

  let userStory: any = {
    category: values.category,
    description: values.description,
    peopleProperties,
    roleAccesses,
    checkBoxEmail,
    emails,
    checkBoxNotification,
    notifications,
  };

  // in creation of user story
  if (specificationId !== undefined) {
    userStory = {
      ...userStory,
      specification: `/mis/specifications/${specificationId}`,
    };
  }

  // in edition of user story
  if (values.id !== undefined) {
    userStory = {
      ...userStory,
      id: values.id,
    };
  }

  return userStory;
};

export { userStoryFactory };

const userStoryFactoryForm = (valuesForm: any) => {
  const {
    id,
    category,
    description,
    peopleProperties,
    roleAccesses,
    currentEmail,
    currentNotification,
  } = valuesForm;
  let userStory: any = {
    id,
    category,
    description,
    peopleProperties: (peopleProperties || []).map((peopleProperty: any) => ({
      peopleProperty,
    })),
    roleAccesses: (roleAccesses || []).map(
      ({ group, locationProperty }: any) => ({
        group: { value: _.get(group, "@id"), label: _.get(group, "name") },
        locationProperty,
      })
    ),
  };

  if (currentEmail !== undefined && currentEmail !== null) {
    userStory = {
      ...userStory,
      checkBoxEmail: true,
      objectEmail: currentEmail.object,
      bodyEmail: currentEmail.body,
      peoplePropertyEmail: (currentEmail.peopleProperties || []).map(
        (peopleProperty: any) => ({
          peopleProperty,
        })
      ),
      emailFollower: currentEmail.follower,
      roleRecipientEmail: (currentEmail.recipientAccesses || []).map(
        ({ group, locationProperty }: any) => ({
          group: { value: _.get(group, "@id"), label: _.get(group, "name") },
          locationProperty,
        })
      ),
      roleCopyEmail: (currentEmail.copyAccesses || []).map(
        ({ group, locationProperty }: any) => ({
          group: { value: _.get(group, "@id"), label: _.get(group, "name") },
          locationProperty,
        })
      ),
    };
  }
  if (currentNotification !== undefined && currentNotification !== null) {
    userStory = {
      ...userStory,
      checkBoxNotification: true,
      notification: currentNotification.message,
      notificationFollower: currentNotification.follower,
      roleRecipientNotify: (currentNotification.roleToNotify || []).map(
        ({ group, locationProperty }: any) => ({
          group: { value: _.get(group, "@id"), label: _.get(group, "name") },
          locationProperty,
        })
      ),
    };
  }

  return userStory;
};

export { userStoryFactoryForm };
