import React, { ReactNode } from "react";
import useNavigator from "../../utils/navigator";
import useScrollToTop from "../../hooks/useScrollToTop";

interface IProps {
  children: ReactNode;
}

function NavigationWrapper({ children }: IProps) {
  useScrollToTop();
  useNavigator();
  return <div>{children}</div>;
}

export default NavigationWrapper;
