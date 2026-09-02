// mocking function with axios call
export const fetchData = (amount = 1): Promise<{ data: number }> =>
  new Promise<{ data: number }>((resolve) => {
    setTimeout(() => {
      resolve({ data: amount });
    }, 500);
  });
