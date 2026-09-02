import { setConfirmationData } from "../redux/slices/confirmationSlice";
import { useAppDispatch } from "./hooks";

// eslint-disable-next-line import/no-mutable-exports
export let confirmationResolve: ((value: boolean) => void) | null = null;

export const useConfirmation = () => {
  const dispatch = useAppDispatch();

  const confirmation = (
    title: string,
    description: string,
    subDescription?: string
  ): Promise<boolean> => {
    return new Promise((resolve) => {
      confirmationResolve = resolve;
      dispatch(setConfirmationData({ title, description, subDescription }));
    });
  };

  return {
    confirmation,
  };
};
