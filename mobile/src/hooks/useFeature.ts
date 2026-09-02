import { useEffect, useState } from "react";
import { useAppSelector } from "./hooks";
import { isFeatureAuthorised } from "../utils/utils";

interface IProps {
  features: Array<string>;
  requiresAll?: boolean;
}

export const useFeature = (props: IProps) => {
  const { features, requiresAll = false } = props;

  const userFeatures = useAppSelector((state) => state.auth.features);
  const [isAuthorised, setIsAuthorised] = useState(false);

  useEffect(() => {
    const isUserAuthorised = isFeatureAuthorised({
      userFeatures,
      features,
      requiresAll,
    });
    if (isUserAuthorised) {
      setIsAuthorised(true);
    }
  }, [userFeatures, features]);

  return isAuthorised;
};
