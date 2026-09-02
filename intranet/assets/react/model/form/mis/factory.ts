const troubleTicketAdditionalOwnerFactory = (troubleTicket: any, user: any) => {
  const additionalOwners = Object.values(
    troubleTicket.additionalOwners || []
  ).map((owner: any) => owner["@id"]);
  additionalOwners.push(user);

  return {
    "@id": troubleTicket["@id"],
    id: troubleTicket.id,
    additionalOwners,
  };
};

export { troubleTicketAdditionalOwnerFactory };
