import { useEffect, useState } from "react";

export const useEffectWithParam = (
  callback: (isFirstRender: boolean) => void,
  dependency: Array<unknown>
) => {
  const [isFirstRender, setIsFirstRender] = useState(true);

  useEffect(() => {
    if (isFirstRender) {
      setIsFirstRender(false);
    }
    callback(isFirstRender);
  }, dependency);
};
