import { REGEX } from "./regExp";

export const TEXT = {
  login: {
    email: {
      title: "Email *",
      error: "Please enter a valid email address.",
    },
    password: {
      title: "Password *",
      error: "Password must be at least 8 characters long.",
    },
    toast: "Logged In Successfully",
    submit: "Sign In",
  },
  logout: {
    toast: "Successfully logged out",
  },
  language: {
    english: "English",
    french: "Français",
    chinese: "中文",
  },
  profile: {
    menu: "My Profile",
    username: "user BASIC",
  },
  home: {
    breadcrumbs: ["Home"],
    heading: /Results \(\d+\)/,
    noTocAvailable: "No TOC Available",
    cardValues: [
      "#1",
      "customer_for_fur",
      "First TOC",
      REGEX.sentence,
      REGEX.word,
      REGEX.model,
      REGEX.airport,
      "IF 1",
    ],
  },
  tocList: {
    breadcrumbs: ["TOC"],
    noTocAvailable: "No TOC Available",
    cardValues: [
      REGEX.numberWithHash,
      "customer_for_fur",
      REGEX.sentence,
      REGEX.sentence,
      REGEX.word,
      REGEX.model,
      REGEX.airport,
      "IF 1",
    ],
    sortValue: [
      "Sort By Airport (A-Z)",
      "Sort By Airport (Z-A)",
      "Sort By IFactor (Low to High)",
      "Sort By IFactor (High to Low)",
      "Sort By Created Date (Old to New)",
      "Sort By Created Date (New to Old)",
      "Sort By Updated Date (Old to New)",
      "Sort By Updated Date (New to Old)",
    ],
    cardLink: REGEX.tocDetailLink,
  },
  tocDetail: {
    breadcrumbs: ["TOC", REGEX.detailBreadcrumb],
    details: {
      model: {
        title: "Model",
        value: REGEX.model,
      },
      commissionDate: {
        title: "Commissioned At",
        value: REGEX.dateOrDash,
      },
      equipmentRecord: {
        title: "Serial Number",
        value: "SN_001",
      },
      aiport: {
        title: "Airport",
        value: "CDG",
      },
      daysOpen: {
        title: "Days Opened",
        value: REGEX.number,
      },
      unitOperationalStatus: {
        title: "Unit Operational Status",
        value: "MCF",
      },
      timeNmc: {
        title: "Time NMC",
        value: REGEX.number,
      },
      title: "My Long Title",
      description: "My Description",
      assignee: {
        title: "Assignee",
        value: "user CSM",
      },
      technician: {
        title: "Technician",
        value: "user CSM",
      },
      errorCode: {
        title: "Error Codes",
        value: "Code 1",
      },
      serviceActivity: {
        title: "Service Activity",
        value: "Troubleshooting",
      },
      payer: {
        title: "Who Pays",
        value: "Customer (Payable Service)",
      },
      ifactor: {
        title: "IF",
        value: "IF 1",
      },
      createdBy: {
        title: "Created By",
        value: "user CSM",
      },
      createdAt: {
        title: "Created At",
        value: REGEX.date,
      },
      buyer: {
        title: "Buyer",
        value: "AIR DE RIEN",
      },
      endUser: {
        title: "End User",
        value: "customer_for_fur",
      },
      maintainer: {
        title: "Maintainer",
        value: "---",
      },
      hourMeter: {
        title: "Hour Meter",
        value: REGEX.numberOrDash,
      },
      manufacturingLocation: {
        title: "Manufacturing Location",
        value: "location_factory",
      },
      sso: {
        title: "SSO Organisation",
        value: "location_sso",
      },
      ssoService: {
        title: "Service SSO",
        value: REGEX.sentence,
      },
      tags: {
        title: "Tags",
        value: "Involves APU-OFF",
      },
      csr: {
        title: "CSR",
        value: REGEX.number,
      },
      symptoms: {
        title: "Symptoms",
        value: "---",
      },
      rootCause: {
        title: "Root Cause",
        value: "---",
      },
      solution: {
        title: "Solution",
        value: "---",
      },
      contacts: {
        title: "Contacts",
        mainContact: {
          title: "Main Contact",
          value: "Ben DOVER - contract-enduser@extra.net",
        },
        additonalContacts: {
          title: "Additional Contacts",
          value: "Annie POSITION - user-superuser@tld.fr",
        },
      },
      customer: {
        title: "Customer",
        value: "AIR DE RIEN",
      },
      thirdPartyName: {
        title: "Third Party",
        value: "My Third Party",
      },
      thirdPartyRef: {
        title: "Third Party Ref",
        value: "My Third Party Ref",
      },
      serialNumber: {
        title: "Customer Asset Number",
        value: "My Customer Asset Number",
      },
      outdated: {
        title: "Outdated",
        value: REGEX.outdated,
      },
      warranty: {
        title: "Warranty",
        value: /(effective|expired)/i,
      },
      emissionRating: {
        title: "Emission Rating",
        value: "---",
      },
    },
    serialLink: "/er/details/1",
    csrLink: /\/csr\/details\/\d+/,
    status: {
      type: {
        pending: "Pending",
        inProgress: "In Progress",
        suspended: "Suspended",
        solved: "Solved",
        closed: "Closed",
      },
      popup: {
        heading: "Update Status",
        status: {
          label: "Status",
        },
        reason: {
          label: "Reason",
          value: "My Reason",
          error: "Please enter valid Reason.",
        },
        symptoms: {
          label: "Symptoms",
          autoFilledValue: "My Long Title",
          value: "My Long Title",
          error: "Please enter valid Symptoms.",
        },
        rootCause: {
          label: "Root Cause",
          value: "My Root Cause",
          error: "Please enter a valid Root Cause.",
        },
        solution: {
          label: "Solution",
          value: "My Solution",
          error: "Please enter valid Solution.",
        },
        thirdPartyJobDescription: {
          label: "Third Party Job Description",
          value: "My Third Party Job Description",
          error: "Please enter valid Third Party Job Description.",
        },
        thirdPartyHours: {
          label: "Third Party Hours",
          value: "1",
          error: "Please enter valid Third Party Hours.",
        },
      },
    },
    csr: {
      heading: /Linked CSR \(\d+\)/,
      id: /#\d+/,
      status: "Assigned",
      isClosed: "Open",
      title: "My Long Title",
    },
    serviceBulletins: {
      title: "Service Bulletins",
      value: "View",
      popup: {
        heading: "Service Bulletins",
        id: /#\d+/,
        type: "Maintenance",
        status: "Implementation",
        title: "NBL BRAKE PEDAL ADJUSTMENT",
        description: "issue detected",
      },
    },
    unitOperationalStatusPopup: {
      heading: "Update Unit Operational Status",
      unitOperationalStatus: {
        title: "Unit Operational Status",
        autoFilledValue: "Mission Capable Fully - MCF",
        error: "Please enter a valid unit operational status.",
      },
      ifactor: {
        title: "IFactor",
        autoFilledValue: "IF 1",
        error: {
          required: "Please enter a valid ifactor.",
          mcf: "IF 1 can be selected only when the Unit Operational Status is MCF.",
        },
      },
      updatedValue: "MCP",
    },
    factoryFlagPopUp: {
      heading: "Update Factory Flag",
      log: {
        title: "Log *",
        value: "My Log",
        error: "Please enter a valid log.",
      },
      updatedValue: "Open",
    },
  },
  logs: {
    heading: "Discussion",
    addPopup: {
      heading: "Add a Log",
      log: {
        title: "Log *",
      },
      factory: {
        title: "Requires Factory Assistance",
      },
      file: {
        title: "File",
        value: "sample1",
      },
      file2: {
        value: "sample2",
      },
      type: {
        title: "Type",
        values: ["Internal With Notification", "Internal", "External"],
      },
    },
    confirmationPopup: {
      title: "Are you sure?",
      description: "This information will be shared with the customer.",
    },
    log: {
      position: /#\d+/,
      username: "user CSM",
      factory: "FF - Open",
      type: "External",
      filename: "s a m p l e 1 j p e g",
      filename2: "s a m p l e 2 p d f",
      date: /\w+ \d{1,2}, \d{4} at \d{1,2}:\d{2} (AM|PM)/,
    },
  },
  files: {
    heading: "All Files",
    noFiles: "No Files Available.",
    addPopUp: {
      heading: "Add Files",
      file: {
        title: "File",
        file1Value: "sample1",
        file2Value: "sample2",
      },
      error: "Please select at least one file.",
    },
    list: {
      chip: "Regular File",
      type: "Regular File",
      toc: {
        file1: {
          title: "s a m p l e 1 . j p g",
        },
        file2: {
          title: "s a m p l e 2 . p d f",
        },
      },
      csr: {
        file1: {
          title: "s a m p l e 1 j p e g . j p g",
        },
        file2: {
          title: "s a m p l e 2 p d f . p d f",
        },
      },
      fileDescription: "- - -",
    },
  },
  csrDetail: {
    breadcrumbs: ["CSR", REGEX.detailBreadcrumb],
    details: {
      model: {
        title: "Model",
        value: REGEX.model,
      },
      commissionDate: {
        title: "Commissioned At",
        value: REGEX.dateOrDash,
      },
      serialNumber: {
        title: "Serial Number",
        value: "SN_001",
      },
      aiport: {
        title: "Airport",
        value: "CDG",
      },
      daysOpen: {
        title: "Days Opened",
        value: REGEX.number,
      },
      type: {
        title: "Type",
        value: "TOC",
      },
      title: "My Long Title",
      description: "My Description",
      createdBy: {
        title: "Created By",
        value: "user CSM",
      },
      createdAt: {
        title: "Created At",
        value: REGEX.date,
      },
      buyer: {
        title: "Buyer",
        value: "AIR DE RIEN",
      },
      endUser: {
        title: "End User",
        value: "customer_for_fur",
      },
      maintainer: {
        title: "Maintainer",
        value: "---",
      },
      hourMeter: {
        title: "Hour Meter",
        value: REGEX.numberOrDash,
      },
      manufacturingLocation: {
        title: "Manufacturing Location",
        value: "location_factory",
      },
      sso: {
        title: "SSO Organisation",
        value: "location_sso",
      },
      ssoService: {
        title: "Service SSO",
        value: REGEX.sentence,
      },
      plannedAt: {
        title: "Planned At",
        value: REGEX.date,
      },
      toc: {
        title: "TOC",
        value: REGEX.number,
      },
      technicianAccordion: "Technician Info",
      fullName: {
        title: "Full Name",
        value: "user CSM",
      },
      email: {
        title: "Email",
        value: "user-csm@tld.fr",
      },
    },
    serialLink: "/er/details/1",
    tocLink: REGEX.tocDetailLink,
    technicianLink: "/profile/64",
    status: {
      type: {
        pending: "Pending",
        assigned: "Assigned",
        inProgress: "In Progress",
        completed: "Completed",
        closed: "Closed",
      },
      popup: {
        heading: "Update Status",
        status: {
          title: "Status",
          error: "Please select a valid Status.",
        },
      },
    },
    intervention: {
      heading: "Intervention",
      status: {
        title: "Status",
        error: "Please select a valid Status.",
        values: {
          toContine: "To Continue",
          started: "Started",
          solved: "Solved",
          closed: "Closed",
          completed: "Completed",
        },
      },
      startDate: {
        title: "Start Date",
        value: "2025-01-01",
        error: "Please select a valid Start Date.",
      },
      endDate: {
        title: "End Date",
        value: "2025-01-01",
        error: "Please select a valid End Date.",
      },
      hourMeter: {
        title: "Hour Meter",
      },
    },
    factoryFlagPopUp: {
      heading: "Update Factory Flag",
      log: {
        title: "Log *",
        value: "My Log",
        error: "Please enter a valid log.",
      },
      updatedValue: "Open",
    },
  },
  tocFilters: {
    search: {
      redirectUrl: "/toc/details/2",
      error: "Please enter a valid TOC ID.",
    },
    heading: "Filters",
    serviceOrganisation: {
      title: "Service SSO",
      value: "location_sso",
    },
    assignee: {
      title: "Assignee",
      value: "user basic",
    },
    airport: {
      title: "Airport",
      value: "CDG",
    },
    ifactor: {
      title: "IFactor",
    },
    unitOperationalStatus: {
      title: "Unit Operational Status",
    },
    serviceActivity: {
      title: "Service Activity",
    },
    status: {
      title: "Status",
    },
    serialNumber: {
      title: "ER SN#",
      value: "green tag",
    },
    equipmentType: {
      title: "Equipment Type",
      value: "Air Conditioner",
    },
    models: {
      title: "Models",
      value: "catalog_product_2",
    },
    payer: {
      title: "Who Is Paying",
    },
    tags: {
      title: "Tags",
    },
    salesOrganization: {
      title: "Sales Organisation",
    },
    manufacturerLocation: {
      title: "Manufacturer Location",
    },
    createdBy: {
      title: "Created By",
      value: "user basic",
    },
    createdAfter: {
      title: "Created After",
      error:
        "Please ensure the 'Created After' date is not after the 'Created Before' date.",
    },
    createdBefore: {
      title: "Created Before",
    },
    solvedAfter: {
      title: "Solved After",
      error:
        "Please ensure the 'Solved After' date is not after the 'Solved Before' date.",
    },
    solvedBefore: {
      title: "Solved Before",
    },
    late: {
      title: "Late (> 5 days)",
    },
    factoryFlag: {
      title: "Factory Flag",
    },
    factoryFlagRecentlyClosed: {
      title: "Factory Flag Recently Closed",
    },
    survey: {
      title: "With Survey",
    },
    buyer: {
      title: "Buyer",
      value: "AIR DE RIEN",
    },
    country: {
      title: "Country",
      value: "Estonia",
    },
    tocPart: {
      title: "TOC Part",
      value: "1",
    },
    endUser: {
      title: "End User",
      value: "AIR DE RIEN",
    },
    sprPart: {
      title: "SPR Part",
      value: "1",
    },
    maintainer: {
      title: "Maintainer",
      value: "AIR DE RIEN",
    },
    confidential: {
      title: "Confidential",
    },
    title: {
      title: "Short Description",
      value: "My Short Description",
    },
    technician: {
      title: "Technician",
      value: "user service",
    },
    errorCodes: {
      title: "Error Codes",
      value: "My Error Codes",
    },
    assigneeOrTechnician: {
      title: "Technician",
      value: "user service",
    },
    filterCount: "34",
    clearFilterCount: "33",
    filterUrl: "/toc/filter",
  },
  csrFilters: {
    search: {
      redirectUrl: "/csr/details/2",
      error: "Please enter a valid CSR ID.",
    },
    heading: "Filters",
    serviceOrganisation: {
      title: "Service SSO",
      value: "location_sso",
    },
    airport: {
      title: "Airport",
      value: "CDG",
    },
    status: {
      title: "Status",
    },
    serialNumber: {
      title: "ER SN#",
      value: "green tag",
    },
    equipmentType: {
      title: "Equipment Type",
      value: "Air Conditioner",
    },
    models: {
      title: "Models",
      value: "catalog_product_2",
    },
    salesOrganization: {
      title: "Sales Organisation",
    },
    manufacturerLocation: {
      title: "Manufacturer Location",
    },
    createdBy: {
      title: "Created By",
      value: "user basic",
    },
    createdAfter: {
      title: "Created After",
      error:
        "Please ensure the 'Created After' date is not after the 'Created Before' date.",
    },
    createdBefore: {
      title: "Created Before",
    },
    serviceTechnician: {
      title: "Service Technician",
      value: "user basic",
    },
    endUser: {
      title: "End User",
      value: "customer_for_fur",
    },
    completedAfter: {
      title: "Completed After",
      error:
        "Please ensure the 'Completed After' date is not after the 'Completed Before' date.",
    },
    completedBefore: {
      title: "Completed Before",
    },
    closedAfter: {
      title: "Closed After",
      error:
        "Please ensure the 'Closed After' date is not after the 'Closed Before' date.",
    },
    closedBefore: {
      title: "Closed Before",
    },
    country: {
      title: "Country",
    },
    csrType: {
      title: "CSR Type",
    },
    filterCount: "19",
    clearFilterCount: "22",
    filterUrl: "/csr/filter",
  },
  dateRange: {
    invalidValue: "12/34/5678",
    errors: {
      invalid: "Please select a valid date.",
      future: "Future dates are invalid, please choose again.",
    },
  },
  csrList: {
    breadcrumbs: ["CSR"],
    noCsrAvailable: "No CSR Available",
    cardValues: [
      REGEX.numberWithHash,
      "customer_for_fur",
      REGEX.sentence,
      REGEX.sentence,
      REGEX.sentence,
      "TXL-737",
      "CDG",
      REGEX.word,
    ],
    sortValue: [
      "Sort By Airport (A-Z)",
      "Sort By Airport (Z-A)",
      "Sort By Created Date (Old to New)",
      "Sort By Created Date (New to Old)",
      "Sort By Updated Date (Old to New)",
      "Sort By Updated Date (New to Old)",
    ],
    cardLink: "/csr/details/6",
  },
  erList: {
    breadcrumbs: ["ER"],
    noErAvailable: "No Equipment Record Available",
    cardValues: [
      REGEX.word,
      REGEX.sentence,
      REGEX.model,
      REGEX.airport,
      "0 CSR",
      "0 TOC",
    ],
    sortValue: ["Sort By Serial Number (A-Z)", "Sort By Serial Number (Z-A)"],
    cardLink: "/er/details/6",
  },
  erFilters: {
    heading: "Filters",
    serialNumber: {
      title: "ER SN#",
      value: "green tag",
    },
    airport: {
      title: "Airport",
      value: "CDG",
    },
    equipmentType: {
      title: "Equipment Type",
      value: "Air Conditioner",
    },
    models: {
      title: "Models",
      value: "catalog_product_2",
    },
    buyer: {
      title: "Buyer",
      value: "AIR DE RIEN",
    },
    endUser: {
      title: "End User",
      value: "AIR DE RIEN",
    },
    maintainer: {
      title: "Maintainer",
      value: "AIR DE RIEN",
    },
    clearFilterCount: "7",
    filterCount: "7",
    filterUrl: "/er/filter",
  },
  erDetail: {
    breadcrumbs: ["ER", REGEX.detailBreadcrumb],
    details: {
      model: {
        title: "Model",
        value: REGEX.model,
      },
      commissionDate: {
        title: "Commissioned At",
        value: REGEX.dateOrDash,
      },
      type: {
        title: "Type",
        value: REGEX.sentence,
      },
      airport: {
        title: "Airport",
        value: REGEX.airport,
      },
      hourMeter: {
        title: "Hour Meter",
        value: REGEX.numberOrDash,
      },
      state: {
        title: "State",
        value: "ACTIVE",
      },
      serialNumber: {
        title: "Serial Number",
        value: REGEX.word,
      },
      status: {
        title: "Status",
        value: "---",
      },
      emissionRating: {
        title: "Emission Rating",
        value: "---",
      },
      manufacturingLocation: {
        title: "Manufacturing Location",
        value: "location_factory",
      },
      shipDate: {
        title: "Ship Date",
        value: REGEX.dateOrDash,
      },
      gtDate: {
        title: "GT Date",
        value: "---",
      },
      buyer: {
        title: "Buyer",
        value: "AIR DE RIEN",
      },
      endUser: {
        title: "End User",
        value: "customer_for_fur",
      },
      installedOptions: {
        title: "Installed Options",
        value: "---",
      },
      dimensionsHeading: "Dimensions",
      length: {
        title: "Length (mm)",
        value: "---",
      },
      width: {
        title: "Width (mm)",
        value: "---",
      },
      height: {
        title: "Height (mm)",
        value: "---",
      },
      weight: {
        title: "Weight (kg)",
        value: "---",
      },
      openToc: {
        title: "Opened TOC",
        value: REGEX.number,
      },
      closedToc: {
        title: "Closed TOC",
        value: REGEX.number,
      },
    },
  },
  erManual: {
    noManuals: "No Manuals Available",
    cardValues: [
      "1",
      "precise description of this manual",
      REGEX.word,
      "en",
      /\d{4}-\d{2}-\d{2} \d{1,2}:\d{2} (AM|PM)/,
    ],
    cardLink: "/er/details/1/manual/1",
  },
  manual: {
    breadcrumbs: ["ER", REGEX.detailBreadcrumb, "Manual (#2)"],
    details: {
      id: {
        title: "ID",
        value: "2",
      },
      legacyId: {
        title: "Legacy ID",
        value: "17328",
      },
      status: {
        title: "Status",
        value: "Released",
      },
      model: {
        title: "Model",
        value: REGEX.model,
      },
      language: {
        title: "Language",
        value: "en",
      },
      createdAt: {
        title: "Created At",
        value: REGEX.date,
      },
      description: {
        title: "Description",
        value: "precise description of this manual",
      },
      features: {
        title: "Features",
        value: "full list of features of this manual",
      },
      section: {
        mainHeading: "Operation and Parts Manual",
        manual: {
          heading: "Manual Section",
          accordionTitle: "Chapter 2",
          headers: [
            "Actions",
            "Factory Number",
            "Revision",
            "Other Description",
            "Position",
            "Document Number",
            "Type",
            "Category",
          ],
          rows: [
            "",
            "fn-000002",
            "B",
            REGEX.sentence,
            "1",
            "4",
            "PARTS DIAGRAM",
            "Chapter 2",
          ],
        },
        parts: {
          heading: "Parts Diagram",
          accordionTitle: "Bridge",
        },
      },
    },
    manualDocumentLink: "/er/details/2/manual/2/document/4",
  },
  manualDocument: {
    breadcrumbs: ["ER", REGEX.detailBreadcrumb, "Manual (#1)", "Document (#1)"],
    details: {
      position: {
        title: "Position",
        value: "2",
      },
      documentNumber: {
        title: "Document Number",
        value: "1",
      },
      factoryNumber: {
        title: "Factory Number",
        value: "fn-000001",
      },
      revision: {
        title: "Revision",
        value: "A",
      },
      type: {
        title: "Type",
        value: "PARTS DIAGRAM",
      },
      category: {
        title: "Category",
        value: "Chapter 1",
      },
      description: {
        title: "Description",
        value: "REAR HITCH ASSY,STANDARD",
      },
      otherDescription: {
        title: "Other Description",
        value: REGEX.sentence,
      },
      download: "Download",
      parts: {
        title: "Parts List",
        headers: [
          "Position",
          "Part#",
          "Quantity",
          "Unit",
          "Other Description",
          "Preventive",
          "Maintenance",
          "Overhaul",
          "Critical",
        ],
        rows: ["1", "pn-000001", "35", "UM", REGEX.sentence, "", "", "", ""],
      },
    },
  },
  erSearch: {
    search: {
      serialNumber: "green tag",
      redirectUrl: "/er/details/18",
      error: "Please enter a valid Serial Number.",
    },
  },
  erSchematics: {
    noSchematics: "No Schematic Diagrams Available",
    schematic: {
      heading: "Schematic Diagrams",
      cardValues: ["SCHEM, BRAKING", REGEX.model, "LIKER", /Brand : [\w-]+/],
    },
    extraSchematic: {
      heading: "Extra Schematic Diagrams",
      cardValues: ["ELEC SCHEMA,TMX150/APC-312", "1152377"],
    },
  },
  tocCreate: {
    heading: "Add New TOC",
    equipmentRecord: {
      title: "Serial Number",
      value: "sn_001",
      error: "Please enter a valid Serial Number / Customer Asset Number.",
    },
    serialNumber: {
      title: "Customer Asset Number",
      value: "My Customer Asset Number",
    },
    mainContact: {
      title: "Main Contact",
    },
    additionalContacts: {
      title: "Additional Contacts",
      autoFilledValue: "Annie POSITION - user-superuser@tld.fr (B)",
    },
    customer: {
      title: "Customer",
      autoFilledValue: "customer_for_fur",
      value: "AIR DE RIEN",
      error: "Please enter a valid customer",
    },
    airport: {
      title: "Airport *",
      autoFilledValue: "CDG  - Paris",
      value: "CDG",
      error: "Please enter a valid airport.",
    },
    serviceOrganisation: {
      title: "Service SSO",
      value: REGEX.sentence,
      error: "Please enter a valid service SSO.",
    },
    assignee: {
      title: "Assignee *",
      autoFilledValue: "SUPERUSER user - user-superuser@tld.fr",
      value: "CSM",
      error: "Please enter a valid assignee.",
    },
    technician: {
      title: "Technician",
      value: "CSM",
    },
    ifactor: {
      title: "IFactor",
      error: {
        required: "Please enter a valid ifactor.",
        mcf: "IF 1 can be selected only when the Unit Operational Status is MCF.",
      },
    },
    errorCodes: {
      title: "Error Codes",
      value: "Code 1",
    },
    payer: {
      title: "Who Is Paying *",
      error: "Please enter a valid payer.",
    },
    serviceActivity: {
      title: "Service Activity *",
      error: "Please enter a valid service activity.",
    },
    unitOperationalStatus: {
      title: "Unit Operational Status *",
      error: "Please enter a valid unit operational status.",
    },
    tags: {
      title: "Tags",
    },
    hourMeter: {
      title: "Hour Meter",
    },
    thirdPartyName: {
      title: "Third Part",
      value: "My Third Party",
    },
    thirdPartyRef: {
      title: "Third Party Ref",
      value: "My Third Party Ref",
    },
    title: {
      title: "Short Description *",
      value: "My Long Title",
      error: "Please enter a valid short description.",
    },
    description: {
      title: "Description *",
      firstDescription: "My First Description",
      secondDescription: "My Second Description",
      error: "Please enter a valid description.",
    },
    switch: {
      title: "Technician Requested?",
    },
    serviceTechnician: {
      title: "Service Technician",
      autoFilledValue: "SUPERUSER user - user-superuser@tld.fr",
      value: "CSM",
    },
    plannedDate: {
      title: "Planned Date",
      value: "2025-01-01",
    },
    mainFile: {
      title: "Add main file (picture only)",
    },
    regularFile: {
      title: "Add more files (picture, video, pdf…)",
    },
    confidential: {
      title: "Confidential",
    },
    reason: {
      title: "Reason",
      value: "My Reason",
      error: "Please enter a valid reason.",
    },
    tocLink: REGEX.tocDetailLink,
    newCustomer: {
      heading: "Create New Contact",
      crt: {
        title: "CRT",
        value: "CRT",
        error: "Please enter a valid CRT.",
      },
      email: {
        title: "Email Address",
        value: "test@email.com",
        error: "Please enter a valid email address.",
      },
      lastname: {
        title: "Last Name",
        value: "My Last Name",
        error: "Please enter a valid last name.",
      },
      firstname: {
        title: "First Name",
        value: "My First Name",
        error: "Please enter a valid first name.",
      },
      division: {
        title: "Division",
        value: "My Division",
        error: "Please enter a valid division.",
      },
      department: {
        title: "Department",
        value: "My Department",
        error: "Please enter a valid department.",
      },
      jobTitle: {
        title: "Job Title",
        value: "My Job Title",
        error: "Please enter a valid job title.",
      },
      phoneNumber: {
        title: "Phone Number",
        value: "+12345678",
        error: "Please enter a valid phone number.",
      },
      language: {
        title: "Language",
        value: "English",
      },
      country: {
        title: "Country",
        value: "Estonia",
      },
    },
  },
  tocUpdate: {
    heading: "Edit TOC",
    equipmentRecord: {
      title: "Serial Number",
      autoFilledValue: "SN_001",
      value: "SN_001",
      error: "Please enter a valid Serial Number / Customer Asset Number.",
    },
    serialNumber: {
      title: "Customer Asset Number",
      autoFilledValue: "My Customer Asset Number",
      value: "My Customer Asset Number 2",
    },
    mainContact: {
      title: "Main Contact",
      value: REGEX.sentence,
    },
    additionalContacts: {
      title: "Additional Contacts",
      autoFilledValue: "Annie POSITION - user-superuser@tld.fr (B)",
    },
    customer: {
      title: "Customer",
      autoFilledValue: "AIR DE RIEN",
      value: "AIR DE RIEN",
      error: "Please enter a valid customer",
    },
    airport: {
      title: "Airport *",
      autoFilledValue: "CDG  - Paris",
      value: "CDG",
      error: "Please enter a valid airport.",
    },
    serviceOrganisation: {
      title: "Service SSO",
      autoFilledValue: REGEX.sentence,
      value: "location_sso",
      error: "Please enter a valid service SSO.",
    },
    assignee: {
      title: "Assignee *",
      autoFilledValue: "CSM user - user-csm@tld.fr",
      value: "CSM",
      error: "Please enter a valid assignee.",
    },
    technician: {
      title: "Technician",
      autoFilledValue: "CSM user - user-csm@tld.fr",
      value: "CSM",
    },
    ifactor: {
      title: "IFactor",
      autoFilledValue: "IF 1",
      error: {
        required: "Please enter a valid ifactor.",
        mcf: "IF 1 can be selected only when the Unit Operational Status is MCF.",
      },
    },
    errorCodes: {
      title: "Error Codes",
      autoFilledValue: "Code 1",
      value: "Code 1",
    },
    payer: {
      title: "Who Is Paying *",
      autoFilledValue: "Customer (Payable Service)",
      error: "Please enter a valid payer.",
    },
    serviceActivity: {
      title: "Service Activity *",
      autoFilledValue: "Troubleshooting",
      error: "Please enter a valid service activity.",
    },
    unitOperationalStatus: {
      title: "Unit Operational Status *",
      autoFilledValue: "Mission Capable Fully - MCF",
      error: "Please enter a valid unit operational status.",
    },
    tags: {
      title: "Tags",
      autoFilledValue: "Involves APU-OFF",
    },
    hourMeter: {
      title: "Hour Meter",
    },
    thirdPartyName: {
      title: "Third Party",
      autoFilledValue: "My Third Party",
      value: "My Updated Third Party",
    },
    thirdPartyRef: {
      title: "Third Party Ref",
      autoFilledValue: "My Third Party Ref",
      value: "My Updated Third Party Ref",
    },
    title: {
      title: "Short Description *",
      autoFilledValue: "My Long Title",
      value: "My Updated Long Title",
      error: "Please enter a valid short description.",
    },
    description: {
      title: "Description *",
      value: "My Updated Description",
      error: "Please enter a valid description.",
    },
    tocLink: REGEX.tocDetailLink,
    delete: {
      confirmationPopup: {
        title: "Are you sure?",
        description:
          "This action will permanently remove the part. This cannot be undone.",
      },
    },
  },
  settings: {
    heading: "Settings",
    reset: {
      title: "Website Data",
      popup: {
        title: "Are you sure?",
        description:
          "This action will clear cookies, cache, and storage. This cannot be undone.",
      },
      redirectLink: "/login",
    },
  },
  csrUpdate: {
    airport: {
      title: "Airport *",
      autoFilledValue: "CDG  - Paris",
      value: "CDG",
      error: "Please enter a valid airport.",
    },
    title: {
      title: "Title *",
      autoFilledValue: "My Long Title",
      value: "My Updated Title",
      error: "Please enter a valid title.",
    },
    description: {
      title: "Description *",
      value: "My Updated Description",
      error: "Please enter a valid description.",
    },
    serviceTechnician: {
      title: "Service Technician",
      value: "CSM",
    },
    plannedDate: {
      title: "Planned Date",
      value: "2025-01-01",
    },
  },
  tocPartsDetail: {
    heading: "Parts",
    headers: [
      "Part Number",
      "Vendor Part Number",
      "Description",
      "Quantity",
      "Created At",
      "Created By",
      "Defective",
      "Supplier Replaces",
      "Customer Replaces",
      "Quotation Required",
      "Comment",
    ],
    values: [
      "1",
      "2",
      "my description",
      "1",
      REGEX.date,
      "user SUPERUSER",
      "",
      "",
      "",
      "",
      "my comment",
    ],
    links: {
      create: /\/toc\/details\/\d+\/parts\/new/,
      update: /\/toc\/details\/\d+\/parts\/edit\/\d+/,
    },
    delete: {
      heading: "Are you sure?",
      description:
        "This action will permanently remove the part. This cannot be undone.",
    },
    spr: {
      heading: "Spare Part Requests",
      headers: [
        "SPR Parts",
        "Description",
        "Quantity",
        "Created At",
        "Created By",
        "SPR",
        "Status",
        "Comment",
        "Tracking Number",
      ],
      values: [
        REGEX.word,
        REGEX.word,
        "1",
        REGEX.date,
        "user SUPERUSER",
        REGEX.numberWithHash,
        "PENDING",
        "My Comment",
      ],
    },
  },
  tocPartsCreate: {
    heading: "Add New TOC Part",
    partNumber: {
      title: "Part Number",
      error: "Please enter a valid part number or vendor part number.",
      value: "1",
    },
    vendorPartNumber: {
      title: "Vendor Part Number",
      value: "2",
    },
    serialNumber: {
      title: "Serial Number",
      value: "3",
    },
    description: {
      title: "Description",
      error: "Please enter a valid description.",
      value: "My Description",
    },
    quantity: {
      title: "Quantity",
      value: "1",
    },
    comment: {
      title: "Comment",
      value: "My Comment",
    },
    defective: {
      title: "Defective",
    },
    replacement: {
      title: "Replacement",
    },
    detailLink: REGEX.tocPartsDetailLink,
  },
  tocPartsUpdate: {
    heading: "Edit TOC Part",
    partNumber: {
      title: "Part Number",
      autoFilledValue: "1",
      value: "11",
    },
    vendorPartNumber: {
      title: "Vendor Part Number",
      autoFilledValue: "2",
      value: "22",
    },
    serialNumber: {
      title: "Serial Number",
      autoFilledValue: "3",
      value: "33",
    },
    description: {
      title: "Description",
      autoFilledValue: "my description",
      value: "My Description Updated",
    },
    quantity: {
      title: "Quantity",
      autoFilledValue: "1",
      value: "11",
    },
    comment: {
      title: "Comment",
      autoFilledValue: "my comment",
      value: "My Comment Updated",
    },
    defective: {
      title: "Defective",
      autoFilledValue: true,
    },
    replacement: {
      title: "Replacement",
      value: "Supplier Replaces",
    },
    detailLink: REGEX.tocPartsDetailLink,
  },
  csrSurvey: {
    openIntervention: {
      status: {
        title: "Status",
        error: "Please select a valid Status.",
      },
      startDate: {
        title: "Start Date",
        value: "2025-01-01",
        error: "Please select a valid Start Date.",
      },
      question1: {
        title: "General Aspect Of The Unit As Presented On Site Rating",
        value: 0,
        error: "Please Select An Option",
      },
      comment1: {
        title: "General Aspect Of The Unit As Presented On Site Comments",
        value: "Comment 1",
        error: "Please enter a valid comment",
      },
      question2: {
        title:
          "Conformity/Compliance With The Specifications (Including Options) Rating",
        value: 0,
        error: "Please Select An Option",
      },
      comment2: {
        title:
          "Conformity/Compliance With The Specifications (Including Options) Comment",
        value: "Comment 2",
        error: "Please enter a valid comment",
      },
      question3: {
        title: "Unit Operational At First Start Rating",
        value: 0,
        error: "Please Select An Option",
      },
      comment3: {
        title: "Unit Operational At First Start Comment",
        value: "Comment 3",
        error: "Please enter a valid comment",
      },
      question4: {
        title: "Shipping Damages",
        options: [
          "No Shipping Damages",
          "Pure Transportation Root Cause",
          "Due To Factory Lack Of Protection",
        ],
        value: [0],
        error: "Please Select An Option",
      },
      comment4: {
        title: "Shipping Damages Comment",
        value: "Comment 4",
        error: "Please enter a valid comment",
      },
      question5: {
        title: "Is Unit Sending Data To LINK FMS ?",
      },
      comment5: {
        title: "Comment",
        value: "Comment 5",
      },
    },
    closeIntervention: {
      endDate: {
        title: "End Date",
        value: "2025-01-01",
        error: "Please select a valid End Date.",
      },
      question1: {
        autoFilledValue: 0,
        value: 1,
      },
      comment1: {
        autoFilledValue: "Comment 1",
        value: "Updated Comment 1",
      },
      question2: {
        autoFilledValue: 0,
        value: 1,
      },
      comment2: {
        autoFilledValue: "Comment 2",
        value: "Updated Comment 2",
      },
      question3: {
        autoFilledValue: 0,
        value: 1,
      },
      comment3: {
        autoFilledValue: "Comment 3",
        value: "Updated Comment 3",
      },
      question4: {
        autoFilledValue: [0],
        value: [0, 1],
      },
      comment4: {
        autoFilledValue: "Comment 4",
        value: "Updated Comment 4",
      },
      comment5: {
        autoFilledValue: "Comment 5",
        value: "Updated Comment 5",
      },
    },
    survey: {
      heading: "Survey",
      startDate: {
        title: "Start Date",
        value: "2025-01-01",
        error: "Please select a valid Start Date.",
      },
      question1: {
        title: "General Aspect Of The Unit As Presented On Site Rating",
        autoFilledValue: 1,
        value: 2,
      },
      comment1: {
        title: "General Aspect Of The Unit As Presented On Site Comments",
        autoFilledValue: "Updated Comment 1",
        value: "Updated Comment Again 1",
        error: "Please enter a valid comment",
      },
      question2: {
        title:
          "Conformity/Compliance With The Specifications (Including Options) Rating",
        autoFilledValue: 1,
        value: 2,
      },
      comment2: {
        title:
          "Conformity/Compliance With The Specifications (Including Options) Comment",
        autoFilledValue: "Updated Comment 2",
        value: "Updated Comment Again 2",
        error: "Please enter a valid comment",
      },
      question3: {
        title: "Unit Operational At First Start Rating",
        autoFilledValue: 1,
        value: 2,
      },
      comment3: {
        title: "Unit Operational At First Start Comment",
        autoFilledValue: "Updated Comment 3",
        value: "Updated Comment Again 3",
        error: "Please enter a valid comment",
      },
      question4: {
        title: "Shipping Damages",
        options: [
          "No Shipping Damages",
          "Pure Transportation Root Cause",
          "Due To Factory Lack Of Protection",
        ],
        autoFilledValue: [1],
        value: [1, 2],
        error: "Please Select An Option",
      },
      comment4: {
        title: "Shipping Damages Comment",
        autoFilledValue: "Updated Comment 4",
        value: "Updated Comment Again 4",
        error: "Please enter a valid comment",
      },
      question5: {
        title: "Is Unit Sending Data To LINK FMS ?",
      },
      comment5: {
        title: "Comment",
        autoFilledValue: "Updated Comment 5",
        value: "Updated Comment Again 5",
      },
    },
    csrStatus: {
      inProgress: "In Progress",
      assigned: "Assigned",
      completed: "Completed",
    },
    statusOptions: {
      started: "Started",
      solved: "Solved",
    },
  },
  tocSprCreate: {
    heading: "Add New TOC SPR",
    parts: {
      part1: {
        title: "Part #1",
        partNumber: {
          title: "Part Number",
          error: "Please enter a valid part number",
          value: "TEMP1001",
        },
        quantity: {
          title: "Quantity",
          autoFilledValue: "1",
          error: "Please enter a valid quantity",
          value: "10",
        },
        uom: {
          title: "UOM",
          autoFilledValue: "EA",
          error: "Please enter a valid uom",
          value: "CM",
        },
        comment: {
          title: "Comment",
          value: "My Comment 1",
        },
      },
      part2: {
        title: "Part #2",
        partNumber: {
          value: "TEMP1002",
        },
        quantity: {
          value: "100",
        },
        uom: {
          value: "M",
        },
        comment: {
          value: "My Comment 2",
        },
      },
      part3: {
        title: "Part #1",
        partNumber: {
          value: "TEMP1003",
        },
        quantity: {
          value: "1000",
        },
        uom: {
          value: "KM",
        },
        comment: {
          value: "My Comment 3",
        },
      },
    },
    address: {
      title: "Address",
      existing: {
        type: {
          title: "Type",
          value: "Existing Address",
        },
        address: {
          title: "Delivery Address",
          value: 0,
        },
        deliveryNotes: {
          title: "Delivery Notes",
          value: "My Delivery Notes",
        },
      },
      new: {
        type: {
          value: "New Address",
        },
        eContact: {
          title: "E-Contact",
        },
        airport: {
          title: "Airport",
          value: "CDG",
        },
        lastname: {
          title: "Last Name",
          error: "Please enter a valid last name",
          value: "My Lastname",
        },
        firstname: {
          title: "First Name",
          error: "Please enter a valid first name",
          value: "My Firstname",
        },
        company: {
          title: "Company",
          error: "Please enter a valid company",
          value: "My Company",
        },
        street1: {
          title: "Street 1",
          error: "Please enter a valid street 1",
          value: "My Street 1",
        },
        street2: {
          title: "Street 2",
          value: "My Street 2",
        },
        telephone: {
          title: "Telephone",
          error: "Please enter a valid telephone",
          value: "My Telephone",
        },
        postalCode: {
          title: "Postal Code",
          error: "Please enter a valid postal code",
          value: "My Postal Code",
        },
        town: {
          title: "Town",
          value: "My Town",
        },
        city: {
          title: "City",
          error: "Please enter a valid city",
          value: "My City",
        },
        state: {
          title: "State",
          value: "My State",
        },
        country: {
          title: "Country",
          error: "Please enter a valid country",
          value: "Estonia",
        },
        deliveryNotes: {
          value: "My Delivery Notes",
        },
      },
    },
    detailLink: REGEX.tocPartsDetailLink,
  },
};
