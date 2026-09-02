export class ValidationError extends Error {
  violations: any;

  constructor(violations = [], ...args: Array<any>) {
    super(...args);

    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, ValidationError);
    }

    this.violations = violations;
  }
}
