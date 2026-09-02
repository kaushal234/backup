declare global {
  interface ServiceWorkerRegistration {
    sync?: {
      register?: (tag: string) => Promise<void>;
    };
  }
}

export default {};
