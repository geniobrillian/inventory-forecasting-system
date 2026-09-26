**Smart Inventory & Demand Forecasting System**

This document defines the database architecture, entities, relationships, data integrity rules, and database conventions for the Smart Inventory & Demand Forecasting System.

The product requirements are defined in:

PRD.md

The application architecture is defined in:

docs/ARCHITECTURE.md

The engineering rules are defined in:

AGENTS.md

This document is the primary reference for:

- database entities
- table responsibilities
- relationships
- inventory data integrity
- historical data
- constraints
- indexes
- database conventions

**1\. Database Technology**

Primary database:

MySQL

MariaDB may be used if compatible with the Laravel version and required database features.

The application must use Laravel migrations for schema management.

Never manually modify the production database schema without a corresponding migration.

**2\. Database Design Principles**

The database must prioritize:

1. Data integrity
2. Auditability
3. Referential integrity
4. Inventory consistency
5. Query performance
6. Maintainability
7. Clear relationships
8. Reproducible migrations

Avoid unnecessary denormalization unless there is a measurable performance reason.

**3\. Core Domain Entities**

The initial database consists conceptually of:

Users

Roles

Permissions

Categories

Units

Products

Suppliers

Warehouses

Inventory Stocks

Inventory Transactions

Purchases

Purchase Items

Sales

Sale Items

Demand Forecasts

Notifications

Settings

Conceptual relationship:

┌──────────────┐

│ Category │

└──────┬───────┘

│

▼

┌──────────────┐

│ Product │

└──────┬───────┘

│

┌─────────────────┼──────────────────┐

▼ ▼ ▼

Inventory Stock Inventory Transaction Forecast

│ │

▼ ▼

Warehouse Warehouse

Supplier

│

▼

Purchase

│

▼

Purchase Item

│

▼

Product

Sale

│

▼

Sale Item

│

▼

Product

**4\. Naming Conventions**

Use Laravel conventions.

**Tables**

Use plural snake\_case:

products

inventory\_stocks

inventory\_transactions

purchase\_items

demand\_forecasts

**Primary Keys**

Default:

id

Use Laravel's standard primary key convention unless there is a strong reason otherwise.

**Foreign Keys**

Use:

product\_id

warehouse\_id

supplier\_id

category\_id

**Timestamps**

Use:

created\_at

updated\_at

for normal mutable entities.

Historical transaction records may additionally use:

transaction\_at

or an equivalent domain-specific timestamp.

**5\. UUID vs Auto Increment**

Initial implementation should use Laravel's conventional auto-incrementing integer/bigint primary keys unless there is a clear requirement for UUID/ULID.

Do not introduce UUID/ULID globally merely for architectural fashion.

If external-facing identifiers are required later, they may be introduced separately.

**6\. Users**

Table:

users

Purpose:

Stores authenticated application users.

Typical fields:

id

name

email

password

email\_verified\_at

remember\_token

created\_at

updated\_at

Additional profile fields should only be added when required.

**7\. Roles**

If role-based authorization is implemented, roles should be represented using the selected Laravel-compatible authorization approach.

Conceptually:

roles

permissions

role\_user

permission\_role

The exact implementation may use a mature authorization package if approved.

Do not create a custom permission system if an existing project dependency already provides the required functionality.

**8\. Categories**

Table:

categories

Purpose:

Groups products into logical categories.

Conceptual fields:

id

name

code

description

is\_active

created\_at

updated\_at

deleted\_at

Recommended constraints:

code UNIQUE

Category names should be unique if the business requirement requires it.

**9\. Units**

Table:

units

Purpose:

Defines the measurement unit of products.

Examples:

PCS

BOX

KG

LITER

PACK

Conceptual fields:

id

name

code

description

is\_active

created\_at

updated\_at

Recommended:

code UNIQUE

**10\. Products**

Table:

products

Purpose:

Stores the master definition of inventory items.

Conceptual fields:

id

sku

barcode

name

category\_id

unit\_id

description

cost\_price

selling\_price

minimum\_stock

lead\_time\_days

is\_active

created\_at

updated\_at

deleted\_at

Important rules:

sku must be unique

cost\_price >= 0

selling\_price >= 0

lead\_time\_days >= 0

minimum\_stock >= 0

**11\. Product SKU**

SKU is the primary business identifier for a product.

Constraint:

products.sku UNIQUE

SKU should not be silently changed if historical transactions depend on it.

If SKU changes are allowed, the change must preserve historical relationships because transactions should reference the product by product\_id.

**12\. Product Barcode**

Barcode should be unique when present.

Recommended:

barcode UNIQUE

However, the field may be nullable if not every product has a barcode.

Do not force barcode presence unless required by the PRD.

**13\. Suppliers**

Table:

suppliers

Purpose:

Stores supplier master data.

Conceptual fields:

id

code

name

contact\_person

phone

email

address

is\_active

created\_at

updated\_at

deleted\_at

Recommended:

code UNIQUE

Supplier deletion should normally use deactivation/soft delete rather than destroying historical purchasing relationships.

**14\. Warehouses**

Table:

warehouses

Purpose:

Represents physical or logical inventory locations.

Conceptual fields:

id

code

name

address

description

is\_active

created\_at

updated\_at

deleted\_at

Recommended:

code UNIQUE

A warehouse should not be hard-deleted if it has historical inventory transactions.

**15\. Inventory Stocks**

Table:

inventory\_stocks

Purpose:

Stores the current inventory state of a product at a warehouse/location.

Conceptual fields:

id

product\_id

warehouse\_id

quantity

reserved\_quantity

created\_at

updated\_at

If location-level inventory is required later:

location\_id

may be introduced.

**16\. Inventory Stock Uniqueness**

For the initial warehouse-level inventory model:

UNIQUE(product\_id, warehouse\_id)

This ensures that a product has one current inventory record per warehouse.

Example:

Product A + Warehouse A

Product A + Warehouse B

Product B + Warehouse A

Each combination represents one current stock record.

**17\. Available Stock**

Do not necessarily store available\_quantity as an independent source of truth.

Prefer deriving it when possible:

available\_quantity =

quantity - reserved\_quantity

If performance later requires persistence, the implementation must ensure that it cannot drift from the underlying quantities.

**18\. Inventory Quantity Rules**

Inventory quantities must not become negative unless negative inventory is explicitly allowed by the PRD.

Default rule:

quantity >= 0

Stock Out must verify available stock before modifying inventory.

**19\. Inventory Transactions**

Table:

inventory\_transactions

Purpose:

Immutable historical ledger of inventory movements.

Conceptual fields:

id

product\_id

warehouse\_id

transaction\_type

quantity

stock\_before

stock\_after

reference\_type

reference\_id

notes

performed\_by

transaction\_at

created\_at

updated\_at

Potential transaction types:

PURCHASE

SALE

TRANSFER\_IN

TRANSFER\_OUT

ADJUSTMENT\_IN

ADJUSTMENT\_OUT

RETURN\_IN

RETURN\_OUT

The exact list should be implemented as a controlled domain value.

**20\. Inventory Transaction Immutability**

Inventory transactions are historical records.

They should generally not be edited or deleted.

Incorrect historical inventory should be corrected through a new transaction.

Example:

Original:

STOCK\_OUT

quantity = 10

Correction:

ADJUSTMENT\_IN

quantity = 2

This preserves audit history.

**21\. Inventory Transaction References**

Transactions may reference the source business operation.

Examples:

PURCHASE

reference\_type = Purchase

reference\_id = 123

or:

SALE

reference\_type = Sale

reference\_id = 456

For transfers:

TRANSFER\_OUT

reference\_type = StockTransfer

reference\_id = 789

The exact polymorphic implementation may evolve.

The important rule is that inventory movement should remain traceable to its originating business operation.

**22\. Inventory Stock Calculation**

For a transaction:

stock\_before

represents inventory before the operation.

stock\_after

represents inventory after the operation.

Example:

Stock Before = 100

Stock In = 20

Stock After = 120

For Stock Out:

Stock Before = 100

Stock Out = 20

Stock After = 80

These values must be calculated inside the same database transaction as the inventory update.

**23\. Inventory Transaction Consistency**

The following invariant must always hold:

inventory\_stocks.quantity

must represent the current balance after all applicable inventory transactions.

An inventory-changing operation must update:

inventory\_stocks

and create:

inventory\_transactions

atomically.

**24\. Stock In Database Flow**

Conceptually:

BEGIN TRANSACTION

LOCK inventory\_stocks row

Read current quantity

Calculate:

stock\_after = stock\_before + quantity

Update inventory\_stocks

Create inventory\_transaction

COMMIT

If any operation fails:

ROLLBACK

**25\. Stock Out Database Flow**

Conceptually:

BEGIN TRANSACTION

LOCK inventory\_stocks row

Read available quantity

Validate sufficient stock

Calculate:

stock\_after = stock\_before - quantity

Update inventory\_stocks

Create inventory\_transaction

COMMIT

If stock is insufficient:

ROLLBACK

**26\. Stock Transfer Database Flow**

A transfer consists of two inventory effects.

Example:

Warehouse A

100

Warehouse B

50

Transfer:

30

Result:

Warehouse A = 70

Warehouse B = 80

Database operation:

BEGIN TRANSACTION

LOCK source stock

LOCK destination stock

Validate source quantity

Update source:

100 → 70

Create:

TRANSFER\_OUT = 30

Update destination:

50 → 80

Create:

TRANSFER\_IN = 30

COMMIT

If any step fails:

ROLLBACK

**27\. Purchases**

Table:

purchases

Purpose:

Stores purchase orders or purchasing transactions.

Conceptual fields:

id

purchase\_number

supplier\_id

warehouse\_id

status

purchase\_date

expected\_date

subtotal

discount

tax

total

notes

created\_by

created\_at

updated\_at

Possible statuses:

DRAFT

ORDERED

PARTIALLY\_RECEIVED

RECEIVED

CANCELLED

The exact status workflow should follow the PRD.

**28\. Purchase Items**

Table:

purchase\_items

Purpose:

Stores products included in a purchase.

Conceptual fields:

id

purchase\_id

product\_id

quantity

received\_quantity

unit\_cost

subtotal

created\_at

updated\_at

Rules:

quantity > 0

unit\_cost >= 0

received\_quantity >= 0

received\_quantity <= quantity

unless the business process explicitly permits over-receiving.

**29\. Purchase Receiving**

Creating a purchase order must not automatically increase inventory.

Inventory increases when goods are received.

Flow:

Purchase Order

↓

Receiving

↓

Stock In

↓

Inventory Transaction

This prevents inventory from being inflated by orders that have not physically arrived.

**30\. Sales**

Table:

sales

Purpose:

Stores customer sales transactions.

Conceptual fields:

id

invoice\_number

warehouse\_id

sale\_date

status

subtotal

discount

tax

total

notes

created\_by

created\_at

updated\_at

Possible statuses:

DRAFT

COMPLETED

CANCELLED

The exact workflow should follow the PRD.

**31\. Sale Items**

Table:

sale\_items

Purpose:

Stores products included in a sale.

Conceptual fields:

id

sale\_id

product\_id

quantity

unit\_price

subtotal

created\_at

updated\_at

Rules:

quantity > 0

unit\_price >= 0

**32\. Sales and Inventory**

A completed sale that affects physical stock must produce a Stock Out transaction.

Flow:

Sale

↓

Sale Items

↓

Stock Out

↓

Inventory Transaction

The sale completion and inventory deduction should be atomic when they occur as one business operation.

**33\. Demand Forecasts**

Table:

demand\_forecasts

Purpose:

Stores generated demand forecasts.

Conceptual fields:

id

product\_id

warehouse\_id

forecast\_date

forecast\_period

method

forecast\_quantity

alpha

window\_size

generated\_at

created\_at

updated\_at

The exact fields may evolve as forecasting requirements become clearer.

**34\. Forecast Method**

Supported initial methods:

MOVING\_AVERAGE

EXPONENTIAL\_SMOOTHING

Store the method used with each forecast result.

This makes historical forecasts explainable.

**35\. Forecast Configuration**

Configuration may include:

window\_size

alpha

forecast\_horizon

Example:

method = MOVING\_AVERAGE

window\_size = 7

forecast\_horizon = 30

For Exponential Smoothing:

method = EXPONENTIAL\_SMOOTHING

alpha = 0.3

forecast\_horizon = 30

**36\. Forecast Data Granularity**

The system should define forecast granularity explicitly.

Initial recommendation:

daily

Historical demand should use the same time granularity as the forecast calculation.

Do not mix daily demand with monthly forecasts without an explicit aggregation/conversion step.

**37\. Historical Demand**

Forecasting should derive demand from actual demand-producing transactions.

Default:

SALE

Do not treat:

PURCHASE

TRANSFER\_IN

TRANSFER\_OUT

ADJUSTMENT

as customer demand.

Transfers may be useful for inventory planning but should not automatically be interpreted as demand.

**38\. Forecast Accuracy Data**

If forecast accuracy is persisted, a dedicated table may be introduced:

forecast\_evaluations

Possible fields:

id

forecast\_id

actual\_quantity

absolute\_error

percentage\_error

evaluated\_at

This should only be introduced when the feature is implemented.

Do not create unused tables prematurely.

**39\. Stock Health**

Stock health may be calculated dynamically rather than persisted.

Possible states:

HEALTHY

WARNING

CRITICAL

OUT\_OF\_STOCK

Inputs may include:

current\_stock

available\_stock

safety\_stock

reorder\_point

forecast\_demand

If stock health becomes expensive to calculate at scale, a materialized/summary table may be introduced later.

**40\. Reorder Configuration**

Product-level configuration may include:

lead\_time\_days

minimum\_stock

Additional configuration may eventually be stored in a dedicated table if requirements become more complex.

Do not create a generic configuration table unless there is a clear need.

**41\. Safety Stock**

Safety Stock does not need to be persisted initially if it can be deterministically calculated.

Prefer:

Forecast / Reorder Service

↓

Calculate Safety Stock

rather than storing duplicated calculated values.

Persist calculated values only when there is a business or performance requirement.

**42\. Reorder Point**

ROP can similarly be calculated dynamically:

ROP =

Forecast Demand During Lead Time

+

Safety Stock

If historical snapshots of ROP are required later, a dedicated planning snapshot table may be introduced.

**43\. Restock Recommendations**

Restock recommendations may initially be generated dynamically.

Conceptual data:

product

warehouse

current\_stock

forecast\_demand

lead\_time

safety\_stock

reorder\_point

recommended\_quantity

reason

A persistent table should only be introduced if recommendations need to be:

- approved
- rejected
- tracked
- converted into purchase orders
- audited historically

**44\. Notifications**

Table:

notifications

Laravel's built-in notification system may be used where appropriate.

If custom inventory alert tracking is required, introduce a dedicated table such as:

inventory\_alerts

or:

notification\_logs

Possible fields:

id

product\_id

warehouse\_id

type

status

sent\_at

created\_at

updated\_at

The implementation should prevent duplicate low-stock alerts.

**45\. Settings**

A settings table may be introduced for configurable business parameters.

Possible structure:

settings

Example:

key

value

type

description

Potential settings:

default\_forecast\_method

default\_forecast\_window

default\_alpha

default\_safety\_factor

default\_forecast\_horizon

Do not store secrets in generic settings.

Secrets belong in environment configuration.

**46\. Soft Delete Strategy**

Soft deletes may be used for master data:

products

categories

suppliers

warehouses

The purpose is to prevent historical relationships from being destroyed.

Historical transactions should generally remain available.

Do not soft-delete transaction records merely to hide mistakes.

Use reversal/adjustment mechanisms instead.

**47\. Foreign Keys**

Use foreign keys for important relationships.

Examples:

products.category\_id

products.unit\_id

inventory\_stocks.product\_id

inventory\_stocks.warehouse\_id

inventory\_transactions.product\_id

inventory\_transactions.warehouse\_id

purchases.supplier\_id

purchases.warehouse\_id

purchase\_items.purchase\_id

purchase\_items.product\_id

sales.warehouse\_id

sale\_items.sale\_id

sale\_items.product\_id

demand\_forecasts.product\_id

demand\_forecasts.warehouse\_id

Use appropriate ON DELETE behavior.

Do not blindly use:

CASCADE

for historical business data.

**48\. Delete Behavior**

Recommended general strategy:

**Master Data**

Use:

soft delete / deactivate

where historical relationships exist.

**Current Inventory**

Do not delete if historical transactions exist.

**Inventory Transactions**

Do not cascade-delete historical transactions.

**Purchase/Sales History**

Do not cascade-delete business history through master-data deletion.

Historical records must remain auditable.

**49\. Index Strategy**

Indexes should support actual query patterns.

Important candidates:

products.sku

products.barcode

inventory\_stocks.product\_id

inventory\_stocks.warehouse\_id

UNIQUE(product\_id, warehouse\_id)

Inventory transaction queries:

inventory\_transactions.product\_id

inventory\_transactions.warehouse\_id

inventory\_transactions.transaction\_type

inventory\_transactions.transaction\_at

Composite indexes may be appropriate:

(product\_id, warehouse\_id)

(product\_id, transaction\_at)

(warehouse\_id, transaction\_at)

Forecast queries:

demand\_forecasts.product\_id

demand\_forecasts.warehouse\_id

demand\_forecasts.forecast\_date

Purchase queries:

purchases.supplier\_id

purchases.warehouse\_id

purchases.purchase\_date

purchases.status

Sales queries:

sales.warehouse\_id

sales.sale\_date

sales.status

Do not add every possible index.

Indexes should correspond to real query patterns.

**50\. Composite Index Considerations**

For historical demand queries, a common query may be:

WHERE product\_id = ?

AND warehouse\_id = ?

AND transaction\_type = 'SALE'

AND transaction\_at BETWEEN ? AND ?

A composite index may be considered:

(product\_id, warehouse\_id, transaction\_type, transaction\_at)

The actual index should be verified against query plans and real usage.

**51\. Numeric Data Types**

Use appropriate decimal types for money.

Example:

DECIMAL(15,2)

Do not use floating point for monetary values.

For quantities, choose precision based on business requirements.

If products can be fractional:

DECIMAL(15,3)

or another appropriate precision may be used.

Do not assume every product quantity is an integer.

**52\. Money**

Money fields should use decimal types.

Examples:

cost\_price

selling\_price

unit\_cost

unit\_price

subtotal

discount

tax

total

Avoid:

FLOAT

DOUBLE

for monetary values.

**53\. Date and Time**

Use Laravel's standard date/time handling.

Inventory transaction timestamps should preserve sufficient precision for auditability.

Use the application's configured timezone consistently.

Business reports must clearly define their reporting timezone.

**54\. Status Fields**

Status values must be controlled.

Avoid arbitrary free-text statuses.

Preferred approaches:

- PHP backed enums
- controlled constants
- validated string values

Example:

PurchaseStatus

SaleStatus

InventoryTransactionType

ForecastMethod

The database representation should remain understandable.

**55\. Enum Strategy**

Do not necessarily use native MySQL ENUM for every domain state.

Prefer application-level enums backed by strings when flexibility and migration simplicity are important.

Example:

PurchaseStatus::DRAFT

PurchaseStatus::ORDERED

PurchaseStatus::RECEIVED

The database stores:

draft

ordered

received

**56\. Auditability**

The following inventory fields are considered important:

stock\_before

stock\_after

quantity

transaction\_type

reference

performed\_by

transaction\_at

These fields make it possible to answer:

Who changed the stock, when, why, and by how much?

**57\. Database Transactions**

The following operations must use database transactions:

Stock In

Stock Out

Stock Transfer

Stock Adjustment

Purchase Receiving

Sale Completion

Whenever multiple writes must succeed together, wrap them in a transaction.

**58\. Row Locking**

When modifying current inventory:

inventory\_stocks

should be locked appropriately during the transaction.

Conceptually:

DB::transaction(function () {

$stock = InventoryStock::query()

\->where(...)

\->lockForUpdate()

\->first();

// calculate and update

});

This prevents race conditions.

**59\. Concurrency Example**

Initial:

Stock = 10

Two users simultaneously attempt:

User A → Stock Out 8

User B → Stock Out 5

Without locking, both may read:

Stock = 10

and incorrectly succeed.

With row locking:

User A

10 → 2

User B

reads 2

attempts -5

→ rejected

This is required for inventory integrity.

**60\. Database Migration Rules**

Every schema change must use a migration.

Examples:

create\_products\_table

create\_inventory\_stocks\_table

create\_inventory\_transactions\_table

add\_lead\_time\_to\_products\_table

Migrations should be:

- ordered
- reproducible
- understandable
- safe to deploy

**61\. Do Not Modify Old Migrations**

Once a migration has been applied to shared/staging/production environments, do not modify it to change the production schema.

Create a new migration.

Example:

2026\_01\_01\_create\_products\_table.php

should not later be rewritten to add new columns.

Instead:

2026\_02\_01\_add\_lead\_time\_to\_products\_table.php

**62\. Seeders**

Seeders should be used for:

- roles
- permissions
- default units
- default categories
- demo data
- development users

Do not put production secrets into seeders.

**63\. Factories**

Factories should exist for testable business entities.

Recommended:

ProductFactory

CategoryFactory

UnitFactory

SupplierFactory

WarehouseFactory

InventoryStockFactory

InventoryTransactionFactory

PurchaseFactory

PurchaseItemFactory

SaleFactory

SaleItemFactory

DemandForecastFactory

Create factories as the corresponding features are implemented.

**64\. Database Testing Strategy**

Business-critical database behavior must be tested.

Examples:

Stock In

Stock Out

Transfer

Adjustment

Purchase Receiving

Sale

Forecast persistence

Tests should verify both:

current state

and:

historical transaction

**65\. Inventory Test Example**

Given:

Stock = 100

Stock In:

+20

Expected:

inventory\_stocks.quantity = 120

and:

inventory\_transaction.quantity = 20

inventory\_transaction.stock\_before = 100

inventory\_transaction.stock\_after = 120

**66\. Stock Out Test Example**

Given:

Stock = 100

Stock Out:

20

Expected:

inventory\_stocks.quantity = 80

and:

stock\_before = 100

stock\_after = 80

**67\. Transfer Test Example**

Given:

Warehouse A = 100

Warehouse B = 50

Transfer:

30

Expected:

Warehouse A = 70

Warehouse B = 80

And two transactions:

TRANSFER\_OUT = 30

TRANSFER\_IN = 30

The operation must be atomic.

**68\. Rollback Test**

Given:

Warehouse A = 20

Warehouse B = 50

Attempt:

Transfer 50

Expected:

operation rejected

and:

Warehouse A = 20

Warehouse B = 50

No partial transaction should remain.

**69\. Demand Query Rules**

Historical demand queries must explicitly filter demand-producing transactions.

Example conceptual query:

product\_id

warehouse\_id

transaction\_type = SALE

transaction\_at between period\_start and period\_end

Do not calculate demand using all inventory transactions.

**70\. Forecast Data Integrity**

A forecast must identify:

product

warehouse

forecast date/period

method

configuration

forecast quantity

This ensures that a historical forecast can be understood later.

**71\. Avoid Premature Tables**

Do not create tables simply because they might be useful someday.

Examples of tables that should only be introduced when required:

forecast\_evaluations

restock\_recommendations

inventory\_metrics

inventory\_alerts

audit\_logs

locations

stock\_reservations

The schema should evolve based on actual requirements.

**72\. Database Evolution**

When a requirement changes:

Requirement

↓

Impact analysis

↓

Migration

↓

Model update

↓

Service update

↓

Tests

Do not modify database structure without checking its impact on:

- models
- relationships
- queries
- reports
- forecasting
- inventory calculations

**73\. Data Integrity Invariants**

The following rules are mandatory.

**Invariant 1**

Every stock-changing operation creates an inventory transaction.

**Invariant 2**

Inventory state and inventory ledger must remain consistent.

**Invariant 3**

Inventory operations must be atomic.

**Invariant 4**

Historical inventory transactions must not be silently deleted.

**Invariant 5**

Stock Out cannot exceed available stock unless explicitly authorized by business rules.

**Invariant 6**

Product SKU must be unique.

**Invariant 7**

Warehouse code must be unique.

**Invariant 8**

Supplier code must be unique.

**Invariant 9**

Inventory stock is unique per product and warehouse.

**Invariant 10**

Money must use decimal types.

**Invariant 11**

Forecast demand must use explicitly defined demand-producing transactions.

**Invariant 12**

Deleting master data must not destroy historical business records.

**74\. Database Relationship Summary**

Conceptual relationships:

Category

│

└── hasMany Products

Unit

│

└── hasMany Products

Product

├── belongsTo Category

├── belongsTo Unit

├── hasMany InventoryStocks

├── hasMany InventoryTransactions

├── hasMany PurchaseItems

├── hasMany SaleItems

└── hasMany DemandForecasts

Supplier

│

└── hasMany Purchases

Warehouse

├── hasMany InventoryStocks

├── hasMany InventoryTransactions

├── hasMany Purchases

└── hasMany Sales

Purchase

└── hasMany PurchaseItems

Sale

└── hasMany SaleItems

**75\. Initial ERD**

Conceptual ERD:

┌──────────────┐

│ categories │

├──────────────┤

│ id │

│ name │

│ code │

└──────┬───────┘

│

│ 1:N

▼

┌──────────────┐

│ products │

├──────────────┤

│ id │

│ sku │

│ name │

│ category\_id │

│ unit\_id │

│ lead\_time │

└──────┬───────┘

│

├─────────────────────┐

│ │

│ 1:N │ 1:N

▼ ▼

┌─────────────────┐ ┌────────────────────────┐

│ inventory\_stocks│ │ inventory\_transactions │

├─────────────────┤ ├────────────────────────┤

│ id │ │ id │

│ product\_id │ │ product\_id │

│ warehouse\_id │ │ warehouse\_id │

│ quantity │ │ transaction\_type │

│ reserved\_qty │ │ quantity │

└────────┬────────┘ │ stock\_before │

│ │ stock\_after │

│ │ performed\_by │

│ └────────────────────────┘

│

│ N:1

▼

┌──────────────┐

│ warehouses │

├──────────────┤

│ id │

│ code │

│ name │

└──────────────┘

┌──────────────┐

│ suppliers │

└──────┬───────┘

│ 1:N

▼

┌──────────────┐

│ purchases │

├──────────────┤

│ id │

│ supplier\_id │

│ warehouse\_id │

│ status │

└──────┬───────┘

│ 1:N

▼

┌─────────────────┐

│ purchase\_items │

├─────────────────┤

│ id │

│ purchase\_id │

│ product\_id │

│ quantity │

│ received\_qty │

│ unit\_cost │

└─────────────────┘

┌──────────────┐

│ sales │

├──────────────┤

│ id │

│ warehouse\_id │

│ status │

└──────┬───────┘

│ 1:N

▼

┌──────────────┐

│ sale\_items │

├──────────────┤

│ id │

│ sale\_id │

│ product\_id │

│ quantity │

│ unit\_price │

└──────────────┘

┌────────────────────┐

│ demand\_forecasts │

├────────────────────┤

│ id │

│ product\_id │

│ warehouse\_id │

│ forecast\_date │

│ method │

│ forecast\_quantity │

└────────────────────┘

**76\. Important Implementation Rule**

The ERD in this document is a conceptual model.

The AI Agent must not create every table immediately.

Implement database entities progressively according to:

PRD.md

+

TASKS.md

+

ARCHITECTURE.md

Only create tables when the corresponding feature is being implemented or when they are a required dependency.

**77\. Database Review Checklist**

Before completing a database-related feature:

\[ \] Migration created

\[ \] Foreign keys reviewed

\[ \] Indexes reviewed

\[ \] Unique constraints reviewed

\[ \] Nullability reviewed

\[ \] Numeric precision reviewed

\[ \] Delete behavior reviewed

\[ \] Transaction behavior reviewed

\[ \] Model relationships implemented

\[ \] Factory updated if necessary

\[ \] Seeder updated if necessary

\[ \] Automated tests added

\[ \] Existing tests still pass

**78\. Final Database Principle**

The most important database principle is:

**Inventory data must be correct, consistent, traceable, and recoverable.**

The database should make it difficult to:

- accidentally lose inventory history
- create inconsistent stock balances
- create duplicate products
- delete historical business records
- perform partial stock transfers
- generate forecasts from incorrectly defined demand

The database schema should remain as simple as possible while enforcing the business invariants required by the system.
