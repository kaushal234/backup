import { useEffect } from "react";

export const useCustomListener = (
  eventName: string,
  callback: (e: CustomEvent<any>) => void
) => {
  useEffect(() => {
    window.addEventListener(eventName, callback as EventListener);
    return () => {
      window.removeEventListener(eventName, callback as EventListener);
    };
  }, []);
};
