import React, { ReactNode } from "react";

interface IProps {
  children: ReactNode;
}

function UnauthenticatedRoute({ children }: IProps) {
  return <div>{children}</div>;
}

export default UnauthenticatedRoute;
