const supportTeamFactory = (values: any) => {
  let supportTeam: any = {
    name: values.name,
  };

  if (values.id !== null) {
    supportTeam = { ...supportTeam, id: values.id };
  }

  return supportTeam;
};

export { supportTeamFactory };

const supportTeamFactoryForm = (values: any) => {
  return {
    id: values.id,
    name: values.name,
  };
};

export { supportTeamFactoryForm };
