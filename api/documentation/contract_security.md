# 📄 Business Documentation – Contract Security

---

## 1. Overview

The contract management system implements a **complex and granular security model** based on:

- **Contract categories**  
  Bank, Customers, IT, M&A, Real Estate, Vendors, Interco, Insurances
- **User roles and permissions** (features)
- **Hierarchical relationships** (supervisor / subordinate – up to 5 levels)
- **Business Units (BU)** and their representatives
- **Divisions** and their representatives
- **Contract confidentiality status**

---

## 2. Global Permissions

### 2.1 Administrator Access

Users with **any** of the following permissions have **full access to all contracts**, regardless of category:

- `MOO_CRS`
- `MKU_CRS`
- `FEATURE_FULL_CONTRACT_ACCESS`

---

## 3. Secured Actions

### 3.1 File Upload / Download

- **Permission:** `CONTRACT_UPLOAD_DOWNLOAD_FILES`
- Allows uploading and downloading files attached to a contract

### 3.2 Contract Editing

- **Voter:** `CONTRACT_EDIT_VOTER`
- Allows modification of contract data

---

## 4. Security Rules by Contract Category

---

### 4.1 Bank Contracts (`BANK`)

#### ✏️ Edit

A user can edit a bank contract if **at least one** condition is met:

- Contract owner
- Contract subscriber
- Has `FEATURE_CATEGORY_BANK_CONTRACT_ACCESS`
- Is representative of a BU linked to the contract
- Is representative of a Division linked to the contract
- Has `FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS_{location_id}`

#### 📎 File Upload / Download

Edit rules **plus at least one** of the following:

- Supervisor of the owner or a subscriber
- Has `FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_BANK_CONTRACT_ACCESS`
    - `FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS`

---

### 4.2 Customer Contracts (`CUSTOMERS`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- ASM (Area Sales Manager) of a linked customer

#### 📎 File Upload / Download

Edit rules **plus**:

- Representative of a linked BU
- Representative of a linked Division
- Supervisor of:
    - Owner
    - Subscriber
    - BU representative
    - ASM
- Has `FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS`
- One subordinate has `FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS`

---

### 4.3 IT Contracts (`IP_IT`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- Has `FEATURE_CATEGORY_IT_CONTRACT_ACCESS`
- Has `FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS_{bu_id}`

#### 📎 File Upload / Download

Edit rules **plus**:

- Supervisor of owner or subscriber
- Has `FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_IT_CONTRACT_ACCESS`
    - `FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS`

---

### 4.4 M&A Contracts (`MA`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- Has `FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS`

#### 📎 File Upload / Download

Edit rules **plus**:

- Supervisor of owner or subscriber
- One subordinate has `FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS`

---

### 4.5 Real Estate Contracts (`REAL_ESTATE`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- BU representative
- Division representative
- Has `FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS_{bu_id}`

#### 📎 File Upload / Download

Edit rules **plus**:

- Supervisor of owner or subscriber
- Has `FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS`
- Has `FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS_{bu_id}`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS`
    - `FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS`
- One subordinate has:
    - `ROLE_CFO`
    - `ROLE_LCM`
    - `ROLE_LGM`

---

### 4.6 Vendor Contracts (`VENDORS`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- Has `FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS`
- BU representative
- Division representative
- Has `FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS_{bu_id}`

#### 📎 File Upload / Download

Edit rules **plus**:

- Supervisor of owner, subscriber, or BU representative
- Has `FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS`
    - `FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS`

---

### 4.7 Interco Contracts (`INTERCO`)

#### ✏️ Edit

- Contract owner
- Contract subscriber
- BU representative
- Division representative
- Has `FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS_{bu_id}`

**Specific sub-categories**  
(`CASH_POOLING_CONTRACT`, `MANAGEMENT_FEES_AGREEMENT`):

- `FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS`
- `FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS_{bu_id}`

#### 📎 File Upload / Download

Edit rules **plus**:

- Supervisor of owner or subscriber
- Has `FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS`
- One subordinate has:
    - `FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS`
    - `ROLE_LGS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS`

**Specific sub-categories:**

- One subordinate has `FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS`
- One subordinate (≤ 5 levels) has:
    - `FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS_{bu_id}`

---

## 5. Confidentiality Management

### 5.1 Non-Confidential Contracts

- `confidential = false`
- Visible to all users with category access

### 5.2 Confidential Contracts

- `confidential = true`
- Visible **only** if the user has:
    - Specific edit or read permissions
    - Appropriate hierarchical relationships
    - Permissions on linked Business Units

---

## 6. Doctrine Extension – `ContractConfidentialExtension`

Automatically applies security filters on:

- **GetCollection**: filters confidential contracts in lists
- **Get**: validates access to a single contract

### Repository methods by category

- `applyBankCategoryRestrictions()`
- `applyCustomerCategoryRestrictions()`
- `applyITCategoryRestrictions()`
- `applyMaCategoryRestrictions()`
- `applyRealEstateCategoryRestrictions()`
- `applyVendorsCategoryRestrictions()`
- `applyIntercoCategoryRestrictions()`
- `applyInsuranceCategoryRestrictions()`

---

## 7. Key Concepts

### 7.1 Owner
The contract owner always has **edit rights**.

### 7.2 Subscriber
Subscribed users obtain **edit rights** via the subscription system.

### 7.3 Business Unit Representative
A BU representative has **specific rights** on contracts linked to their BU.

### 7.4 Hierarchy (Supervisor)
Security checks traverse **up to 5 hierarchy levels**.

### 7.5 Subordinates with Features
Permissions held by subordinates (≤ 5 levels) can grant access to supervisors.

### 7.6 Business Unit Features

BU-scoped permissions format:

- `FEATURE_XXX_{bu_id}`
- `FEATURE_XXX_{location_id}`

### 7.7 Division Representative

A Division representative has the **same rights** as a BU representative on contracts linked to their
Division (visibility, edit, and file access across the same categories). Unlike BU representatives,
these rights are **not** propagated to the Division representative's supervisors.

---

## 8. Permission Summary

### 8.1 Global Permissions

| Permission | Description |
|----------|------------|
| `MOO_CRS` | Full access to all contracts |
| `MKU_CRS` | Full access to all contracts |
| `FEATURE_FULL_CONTRACT_ACCESS` | Full access to all contracts |

---

## 9. Technical Notes

### Hierarchy Depth
Maximum depth: **5 levels**

### Business Unit Identifiers
Permissions can rely on:
- `bu_id`
- `location_id`

### Performance
Optimized SQL queries with joins are used to minimize database load during permission checks.
