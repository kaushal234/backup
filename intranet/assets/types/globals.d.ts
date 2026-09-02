declare global {
    const hinclude:
        | {
        run?: () => void;
    }
        | undefined;

    interface Window {
        mountLogsBlocks?: (root?: ParentNode) => void;
        mountSubscriptions?: (root?: ParentNode) => void;
    }
}

export {};