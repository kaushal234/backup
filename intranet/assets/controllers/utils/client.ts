export async function fetch(url: URL, options?: RequestInit) {
  const locale = document.documentElement.lang || "en";

  const token = window.user?.token ?? null;

  return window.fetch(url, {
    ...options,
    ...{
      headers: {
        Accept: "application/ld+json",
        "Content-type": "application/ld+json",
        Authorization: `Bearer ${token}`,
        "Accept-Language": locale,
      },
    },
  });
}
