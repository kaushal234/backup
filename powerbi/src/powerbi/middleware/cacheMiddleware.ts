import { Request, Response, NextFunction } from "express";

// Middleware to disable caching globally
const cacheMiddleware = (req: Request, res: Response, next: NextFunction) => {
  res.setHeader(
    "Cache-Control",
    "no-store, no-cache, must-revalidate, proxy-revalidate"
  );
  res.setHeader("Pragma", "no-cache"); // For HTTP/1.0 compatibility
  res.setHeader("Expires", "0"); // Expired immediately
  next();
};

export default cacheMiddleware;
