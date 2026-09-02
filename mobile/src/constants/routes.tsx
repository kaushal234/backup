export const ROUTES = {
  home: "/",
  login: "/login",
  azure: "/azure",
  toc: {
    home: "/toc",
    filter: "/toc/filter",
    details: "/toc/details",
    create: "/toc/new",
    update: "/toc/edit",
    search: "/toc/search",
    parts: {
      create: "parts/new",
      update: "parts/edit",
    },
    spr: {
      create: "spr/new",
      update: "spr/edit",
    },
  },
  csr: {
    home: "/csr",
    filter: "/csr/filter",
    details: "/csr/details",
    update: "/csr/edit",
    survey: {
      home: "survey",
    },
  },
  er: {
    home: "/er",
    finder: "/er/finder",
    details: "/er/details",
    filter: "/er/filter",
    manual: {
      home: "manual",
      document: "document",
    },
  },
  profile: "/profile",
  extranetUser: "/extranet_user",
  settings: "/settings",
};
