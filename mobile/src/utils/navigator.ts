import { NavigateFunction, useNavigate } from "react-router";

let navigateFn: NavigateFunction;

const useNavigator = () => {
  const navigate = useNavigate();
  navigateFn = navigate;
  return navigate;
};

export const navigateTo = (path: string) => {
  if (navigateFn) {
    navigateFn(path);
  } else {
    console.error("Navigate function is not initialized.");
  }
};

export default useNavigator;
