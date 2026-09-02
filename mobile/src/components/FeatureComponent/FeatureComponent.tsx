import React, { ReactNode, useEffect, useState } from "react";
import { useAppSelector } from "../../hooks/hooks";
import { isFeatureAuthorised } from "../../utils/utils";

interface IProps {
  children: ReactNode;
  features: Array<string>;
  requiresAll?: boolean;
}

function FeatureComponent({ children, features, requiresAll = false }: IProps) {
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

  if (!isAuthorised) {
    return null;
  }

  return <div>{children}</div>;
}

export default FeatureComponent;
