// Function to redirect to a new URL
// Use as a function to permit mock with tests.
export const navigate = (url) => {
  window.location.assign(url);
};
