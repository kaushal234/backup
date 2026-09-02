import React from "react";

interface IProps {
  user: any;
}

function Subscriber({ user }: IProps) {
  const photo =
    user.photo !== null
      ? `/en/private/uploads/${user.photo.filePath}`
      : "/shared/no_photo.jpg";
  let link = null;

  switch (user["@type"]) {
    case "People":
      link = `/en/private/directory${user["@id"]}/show`;
      break;
    case "ExtranetUser":
      link = `/en/private${user["@id"]}/show`;
      break;
    default:
      return (
        <>
          <img className="img-fluid" width="64" src={photo} alt="user-image" />
          {user.lastname} {user.firstname}
          <br />
        </>
      );
  }

  return (
    <a href={link}>
      <img className="img-fluid" width="64" src={photo} alt="user-image" />
      {user.lastname} {user.firstname}
      <br />
    </a>
  );
}

export default Subscriber;
