export class Component {
  on(_event: string, _callback: () => void): void {}
}

export const getComponent = jest.fn().mockResolvedValue(new Component());
