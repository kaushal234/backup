declare global {
  interface Window {
    user?: {
      token?: string;
    };
  }
  const process: {
    env: Record<string, string | undefined>;
  };
}

export {};
