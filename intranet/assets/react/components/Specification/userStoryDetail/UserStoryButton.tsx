import React from "react";

interface IProps {
  name: any;
  route: any;
  btnColor?: any;
  icon: any;
}

function UserStoryButton({ name, route, btnColor = "primary", icon }: IProps) {
  return (
    <a
      className={`btn btn-${btnColor} p-0 ps-1 pe-1`}
      href={`/en/private/mis/modules/${route}`}
    >
      <i className={`fa fa-${icon} pe-2`} />
      {name}
    </a>
  );
}

export default UserStoryButton;
