**Smart Inventory & Demand Forecasting System**

This document defines the technical architecture and structural conventions of the Smart Inventory & Demand Forecasting System.

The product requirements are defined in:

PRD.md

The rules for AI-assisted development are defined in:

AGENTS.md

This document answers:

How should the system be structured and how should its major components interact?

**1\. Architecture Goals**

The architecture must prioritize:

1. Data integrity
2. Inventory consistency
3. Auditability
4. Maintainability
5. Testability
6. Security
7. Clear separation of responsibilities
8. Reasonable performance
9. Simple deployment
10. Easy future extension

The system should avoid unnecessary complexity.

The architecture should be appropriate for a modular Laravel monolith.

**2\. Architectural Style**

The application uses a:

**Modular Laravel Monolith**

The system remains a single Laravel application and a single deployable unit, but business responsibilities are separated into logical modules.

Conceptually:

┌─────────────────────────┐

│ Web Browser │

└────────────┬────────────┘

│

▼

┌─────────────────────────┐

│ Laravel Routes │

└────────────┬────────────┘

│

▼

┌─────────────────────────┐

│ Controllers │

└────────────┬────────────┘

│

▼

┌─────────────────────────┐

│ Requests / Authorization│

└────────────┬────────────┘

│

▼

┌─────────────────────────┐

│ Services / Actions │

└────────────┬────────────┘

│

┌─────────────────┼─────────────────┐

▼ ▼ ▼

Inventory Domain Forecasting Domain Purchasing

│ │ │

└─────────────────┼─────────────────┘

▼

┌─────────────────────────┐

│ Eloquent Models / Query │

│ Builder / Repositories │

└────────────┬────────────┘

│

▼

┌─────────────────────────┐

│ MySQL │

└─────────────────────────┘

**3\. Technology Stack**

**Backend**

- PHP
- Laravel

**Database**

- MySQL or MariaDB

**Frontend**

- Laravel Blade
- Tailwind CSS
- Alpine.js

**Charts**

- ApexCharts or Chart.js

**Excel Export**

- Maatwebsite Laravel Excel

**PDF**

- Laravel-compatible PDF library

**Queue**

- Laravel Queue

Development may use:

database queue

Production may use:

Redis

if scale requires it.

**Scheduler**

Laravel Scheduler.

**Version Control**

Git.

**4\. Application Type**

The initial application is a server-rendered web application.

Primary rendering:

Laravel Blade

Interactive UI:

Alpine.js

Charts:

ApexCharts / Chart.js

The project should not introduce a separate SPA framework unless explicitly required.

Do not introduce React, Vue, Inertia, or another frontend architecture without an explicit architectural decision.

**5\. High-Level Modules**

The application is logically divided into:

Authentication

Master Data

Inventory

Purchasing

Sales

Forecasting

Replenishment

Notifications

Reports

Dashboard

Settings

Conceptual structure:

Smart Inventory

│

├── Authentication

│

├── Master Data

│ ├── Products

│ ├── Categories

│ ├── Units

│ ├── Suppliers

│ └── Warehouses

│

├── Inventory

│ ├── Stock

│ ├── Transactions

│ ├── Stock In

│ ├── Stock Out

│ ├── Adjustment

│ ├── Transfer

│ └── Stock Card

│

├── Purchasing

│ ├── Purchase Orders

│ └── Purchase Items

│

├── Sales

│ ├── Sales

│ └── Sale Items

│

├── Forecasting

│ ├── Moving Average

│ ├── Exponential Smoothing

│ ├── Forecast Results

│ └── Forecast Accuracy

│

├── Replenishment

│ ├── Safety Stock

│ ├── Reorder Point

│ ├── Stock Health

│ ├── Stockout Prediction

│ └── Restock Recommendation

│

├── Notifications

│ ├── Email

│ └── Webhook

│

├── Reports

│

└── Dashboard

**6\. Laravel Application Layers**

The application should generally follow:

Route

↓

Controller

↓

Form Request / Authorization

↓

Service / Action

↓

Domain Logic

↓

Model / Query

↓

Database

The exact implementation can vary when Laravel conventions provide a simpler solution.

**7\. Routes**

Routes are responsible for mapping HTTP requests to application actions.

Routes should not contain business logic.

Example:

GET /products

POST /products

GET /products/{product}

PUT /products/{product}

Route definitions should remain concise.

Authorization middleware should be applied where appropriate.

**8\. Controllers**

Controllers should coordinate HTTP-level concerns.

Responsibilities:

- receive request
- validate through Form Request
- authorize
- call service/action
- return response
- redirect
- return view

Controllers should not contain complex business calculations.

Avoid:

Controller

├── forecasting formulas

├── stock calculations

├── notification logic

└── complex database transactions

Prefer:

Controller

↓

InventoryService

↓

Database

**9\. Form Requests**

Use Laravel Form Requests for request validation when validation is non-trivial.

Examples:

StoreProductRequest

UpdateProductRequest

StockInRequest

StockOutRequest

TransferStockRequest

CreatePurchaseRequest

Form Requests may also contain authorization checks where appropriate.

**10\. Services**

Services contain reusable business operations that span multiple models or require meaningful business logic.

Recommended services:

InventoryService

ForecastService

ReorderService

StockHealthService

NotificationService

ReportService

Example:

InventoryService

├── stockIn()

├── stockOut()

├── transfer()

└── adjustment()

Services should not be used as dumping grounds.

If an operation is a clearly identifiable business action, consider an Action class.

**11\. Actions**

Actions represent focused business operations.

Examples:

CreateStockIn

CreateStockOut

TransferStock

AdjustStock

CalculateForecast

CalculateSafetyStock

CalculateReorderPoint

GenerateRestockRecommendation

An Action should ideally represent one meaningful operation.

Example:

TransferStock

↓

validate source stock

↓

database transaction

↓

decrease source

↓

create TRANSFER\_OUT

↓

increase destination

↓

create TRANSFER\_IN

**12\. Domain Logic**

Business-critical calculations should not live inside:

- Blade
- JavaScript
- Controllers
- database views

Important calculations include:

Moving Average

Exponential Smoothing

Safety Stock

Reorder Point

Days Until Stockout

Recommended Order Quantity

Inventory Turnover

Stock Health

These should live in dedicated services/classes that can be unit tested.

**13\. Inventory Architecture**

Inventory is the most critical domain in the application.

The architecture uses two related concepts:

Current Inventory State

and:

Inventory Transaction Ledger

**14\. Current Inventory State**

Table:

inventory\_stocks

Purpose:

Fast access to the current inventory state.

Conceptual data:

product\_id

warehouse\_id

location\_id

quantity

reserved\_quantity

available\_quantity

This table should be optimized for current-state queries.

**15\. Inventory Transaction Ledger**

Table:

inventory\_transactions

Purpose:

Historical record of every inventory movement.

Transaction types:

PURCHASE

SALE

TRANSFER\_IN

TRANSFER\_OUT

ADJUSTMENT\_IN

ADJUSTMENT\_OUT

RETURN\_IN

RETURN\_OUT

Each transaction should record:

product

warehouse

location

quantity

stock\_before

stock\_after

transaction\_type

reference

user

timestamp

**16\. Inventory Source-of-Truth Strategy**

The system uses:

inventory\_transactions

as the audit/history source.

And:

inventory\_stocks

as the current state.

The two must remain consistent.

Conceptually:

Transaction Ledger

│

│ produces

▼

Current Inventory State

Do not update current stock without creating the corresponding transaction.

**17\. Inventory Transaction Flow**

Example Stock In:

User

↓

Stock In Form

↓

StockInRequest

↓

CreateStockIn

↓

DB Transaction

├── Lock current inventory

├── Read stock

├── Calculate new stock

├── Update inventory\_stocks

└── Create inventory\_transaction

↓

Commit

If any step fails:

Rollback

**18\. Stock Out Flow**

User

↓

Stock Out Form

↓

StockOutRequest

↓

CreateStockOut

↓

DB Transaction

├── Lock inventory row

├── Validate available quantity

├── Calculate new stock

├── Update inventory\_stocks

└── Create inventory\_transaction

↓

Commit

If available stock is insufficient, the transaction must fail unless the operation is an explicitly authorized adjustment.

**19\. Concurrent Inventory Updates**

Inventory operations must account for concurrent requests.

When appropriate, use database row locking such as:

lockForUpdate()

inside a database transaction.

Example:

DB::transaction(function () {

$stock = InventoryStock::query()

\->where(...)

\->lockForUpdate()

\->first();

// modify stock

});

This prevents two simultaneous operations from incorrectly calculating stock from the same old balance.

**20\. Stock Transfer Architecture**

A transfer is a single business operation with two inventory effects.

Example:

Warehouse A

100

│

│ transfer 30

▼

Warehouse B

50

Result:

Warehouse A = 70

Warehouse B = 80

Database operation:

BEGIN

lock source

lock destination

validate source stock

decrease source

create TRANSFER\_OUT

increase destination

create TRANSFER\_IN

COMMIT

Both sides must succeed or both must fail.

**21\. Inventory Adjustment**

Inventory adjustment is used when physical stock differs from system stock.

Example:

System Stock: 100

Physical Stock: 97

The system creates:

ADJUSTMENT\_OUT

quantity: 3

Do not directly overwrite:

quantity = 97

without creating an audit transaction.

Adjustment should require appropriate authorization.

**22\. Purchasing Architecture**

Purchasing flow:

Supplier

↓

Purchase Order

↓

Purchase Items

↓

Receiving

↓

Stock In

↓

Inventory Transaction

A purchase should not automatically increase inventory merely because a purchase order was created.

Inventory should increase when goods are received.

**23\. Sales Architecture**

Sales flow:

Sale

↓

Sale Items

↓

Stock Out

↓

Inventory Transaction

Sales and inventory changes must remain consistent.

If a sale operation modifies inventory, use a database transaction.

**24\. Forecasting Architecture**

Forecasting is separated from inventory transaction processing.

Conceptual flow:

Inventory Transactions

↓

Historical Demand

↓

Forecast Service

↓

Forecast Result

↓

Replenishment Engine

Forecasting should not directly modify inventory.

**25\. Demand Calculation**

The forecasting engine needs historical demand.

The default demand source is:

SALE

or an explicitly configured equivalent.

Do not automatically treat every inventory transaction as demand.

For example:

PURCHASE

is supply, not demand.

TRANSFER\_OUT

is movement, not necessarily customer demand.

The exact demand definition must remain consistent throughout forecasting.

**26\. Forecast Methods**

Initial supported methods:

MOVING\_AVERAGE

EXPONENTIAL\_SMOOTHING

The architecture should allow future methods to be added without rewriting the entire forecasting module.

Conceptually:

ForecastService

│

├── MovingAverageForecaster

│

└── ExponentialSmoothingForecaster

If appropriate, use an interface/contract:

ForecasterInterface

Example conceptual contract:

forecast(demandHistory, configuration)

**27\. Forecast Persistence**

Forecast results should be persisted.

Table:

demand\_forecasts

Purpose:

- historical forecast analysis
- actual vs forecast comparison
- forecast accuracy
- reproducibility
- dashboard reporting

Forecast records should contain enough metadata to understand how the result was generated.

**28\. Forecast Accuracy**

Forecast accuracy calculations should be separated from forecast generation.

Conceptually:

Forecast

↓

Actual Demand becomes available

↓

Forecast Evaluation

↓

Error Metrics

Possible metrics:

Absolute Error

Percentage Error

MAE

MAPE

The system should avoid calculating misleading percentage errors when actual demand is zero.

**29\. Replenishment Architecture**

Replenishment consumes forecast and inventory information.

Flow:

Current Inventory

+

Forecast

+

Lead Time

+

Safety Stock

↓

Reorder Engine

↓

ROP

↓

Stock Health

↓

Restock Recommendation

**30\. Safety Stock**

Safety Stock calculation belongs to:

ReorderService

or a dedicated:

SafetyStockCalculator

if complexity grows.

The calculation must be deterministic and testable.

**31\. Reorder Point**

Reorder Point is calculated from:

Forecast Demand

+

Lead Time

+

Safety Stock

Base formula:

ROP =

Forecast Daily Demand × Lead Time

+

Safety Stock

The exact formula may evolve as the forecasting model becomes more sophisticated.

Any formula change must be documented in:

docs/DECISIONS.md

**32\. Stock Health Architecture**

Stock health should be centralized.

Recommended component:

StockHealthService

Input:

available\_stock

safety\_stock

reorder\_point

Output:

HEALTHY

WARNING

CRITICAL

OUT\_OF\_STOCK

Do not duplicate the logic across:

- dashboard
- reports
- notifications
- API
- Blade

**33\. Restock Recommendation**

Restock recommendation should be deterministic and explainable.

Input:

current stock

forecast demand

planning horizon

safety stock

lead time

Output:

target stock

recommended quantity

reason

Example:

Current Stock: 70

Forecast: 30/day

Planning Horizon: 30 days

Safety Stock: 30

Target Stock:

30 × 30 + 30

\= 930

Recommended:

930 - 70

\= 860

**34\. Notification Architecture**

Notifications should be decoupled from business logic.

Example:

StockHealthService

↓

Alert Detected

↓

NotificationService

↓

Queue

↓

Notification Channel

├── Email

└── WhatsApp Webhook

The inventory calculation should not block while an external notification is being sent.

**35\. Notification Idempotency**

The notification system must avoid unnecessary duplicates.

Conceptually:

Alert Detected

↓

Already notified?

┌────┴────┐

Yes No

│ │

Skip Create Notification

↓

Queue

Notification history should be persisted.

**36\. Queue Architecture**

Heavy or asynchronous work should use Laravel Queue.

Example:

Scheduler

↓

Dispatch Job

↓

Queue

↓

Worker

↓

Service

Jobs may include:

CalculateDailyForecast

CheckInventoryAlerts

SendInventoryNotification

GenerateInventoryReport

Jobs must be designed to be safely retried.

**37\. Scheduler Architecture**

Laravel Scheduler triggers recurring tasks.

Example:

Scheduler

│

├── Calculate Forecast

├── Update Inventory Metrics

├── Detect Low Stock

├── Detect Dead Stock

└── Process Notifications

The scheduler should dispatch jobs rather than perform large workloads directly.

**38\. Reporting Architecture**

Reports should use dedicated services when calculations become complex.

Example:

ReportController

↓

InventoryReportService

↓

Query / Aggregation

↓

Exporter

├── Excel

└── PDF

Reports must use the same business definitions as dashboards.

**39\. Dashboard Architecture**

The dashboard is a read-oriented presentation layer.

It should not perform expensive business calculations on every page request.

Preferred flow:

Scheduled / Event-driven calculations

↓

Persist metrics where appropriate

↓

Dashboard queries

↓

Charts / KPI

For metrics that are inexpensive to calculate, direct queries are acceptable.

**40\. Caching**

Caching may be introduced for expensive read operations.

Potential candidates:

- dashboard aggregates
- category statistics
- warehouse statistics
- forecast summaries

Do not cache highly volatile inventory data without an explicit invalidation strategy.

Inventory correctness is more important than cache performance.

**41\. Database Transactions**

Use transactions whenever multiple writes must remain atomic.

Examples:

Stock In

Stock Out

Stock Transfer

Purchase Receiving

Sales + Inventory

Inventory Adjustment

General rule:

If partial completion would create inconsistent inventory,

use a database transaction.

**42\. Database Constraints**

Use database constraints to enforce important invariants.

Examples:

products.sku UNIQUE

foreign keys

required fields

appropriate numeric constraints

Application validation remains necessary, but the database should also protect important invariants.

**43\. Soft Deletes**

Use soft deletes selectively.

Suitable candidates may include:

Product

Supplier

Category

Warehouse

Do not blindly use soft deletes for all entities.

Inventory transactions should generally remain historical and immutable.

**44\. External Integrations**

External services should be isolated behind application services.

Example:

NotificationService

↓

WhatsAppWebhookClient

Do not scatter external HTTP calls throughout controllers.

External service credentials must come from environment/configuration.

**45\. API Integration Boundary**

If an external integration is introduced:

Application

↓

Integration Service

↓

External API

Do not let external API-specific logic leak into core inventory business logic.

For example, inventory logic should not know the details of a specific WhatsApp provider.

**46\. Frontend Architecture**

Use reusable Blade components.

Examples:

resources/views/components/

button

input

select

modal

badge

table

pagination

stat-card

alert

Pages should compose reusable components.

Avoid duplicating the same UI structure across multiple pages.

**47\. Frontend State**

Use Alpine.js for small interactive state.

Examples:

- modal
- dropdown
- filters
- confirmation
- tabs
- simple dynamic forms

Do not build a complex client-side state management architecture unless the requirements justify it.

**48\. Authorization Architecture**

Use Laravel authorization mechanisms.

Conceptually:

Authentication

↓

Role / Permission

↓

Policy / Gate

↓

Controller / Action

Authorization should happen before business operations.

**49\. Audit Architecture**

Important business operations should be auditable.

At minimum inventory transactions record:

who

what

when

where

quantity

before

after

reference

Future versions may introduce a generalized audit log.

**50\. Error Handling Architecture**

Expected business errors should be handled gracefully.

Examples:

InsufficientStockException

InvalidStockTransferException

InvalidForecastConfigurationException

Do not expose internal stack traces to normal users.

Unexpected exceptions should be logged and handled through Laravel's exception system.

**51\. Domain Exceptions**

As business complexity grows, domain-specific exceptions may be introduced.

Examples:

InsufficientStockException

InvalidTransferException

ProductInactiveException

InvalidForecastException

Use exceptions where they make the business failure clearer.

Do not create excessive custom exceptions for trivial validation.

**52\. Testing Architecture**

Tests should mirror business boundaries.

Conceptually:

tests/

├── Unit/

│ ├── Forecasting/

│ ├── Replenishment/

│ └── Inventory/

│

└── Feature/

├── Authentication/

├── Products/

├── Inventory/

├── Purchasing/

├── Sales/

└── Reports/

Exact folder structure may follow the Laravel version and existing project conventions.

**53\. Testing Priority**

Highest priority:

Inventory Integrity

Forecasting

Replenishment

Authorization

These areas should have strong automated coverage.

UI-only details may have lighter coverage when appropriate.

**54\. Deployment Architecture**

Initial deployment can use a conventional Laravel server.

Conceptually:

Internet

↓

Web Server

↓

PHP / Laravel

↓

MySQL

Queue worker:

Queue

↓

Laravel Worker

Scheduler:

Cron

↓

Laravel Scheduler

**55\. Production Processes**

Production should run at minimum:

Web Server

PHP Application

Database

Queue Worker

Scheduler

Example:

┌──────────────┐

│ Nginx │

└──────┬───────┘

↓

┌──────────────┐

│ Laravel │

└──────┬───────┘

↓

┌───────┐

│ MySQL │

└───────┘

Queue:

Laravel → Queue Worker

Scheduler:

Cron → Laravel Scheduler

**56\. Environment Separation**

At minimum:

local

staging

production

Environment-specific values must be stored in environment configuration.

Do not commit production secrets.

**57\. Storage**

Laravel filesystem should be used for application-managed files.

Potential files:

- generated reports
- exports
- uploaded documents

Do not store uploaded files directly inside source-controlled directories.

**58\. Observability**

The application should provide enough logging to diagnose:

- failed jobs
- inventory failures
- notification failures
- report generation failures
- external API failures

Production logs must not contain secrets.

**59\. Scalability Strategy**

The first version should remain a modular monolith.

Do not introduce microservices prematurely.

Potential future scaling path:

Laravel Monolith

↓

Queue

↓

Redis

↓

Dedicated workers

↓

Read optimization

↓

Only if necessary:

service extraction

Microservices should only be considered when actual scale or organizational requirements justify them.

**60\. Architecture Evolution**

Architecture may evolve.

Any significant architectural change should:

1. Explain the problem.
2. Describe the proposed solution.
3. Consider alternatives.
4. Document the decision.
5. Update this file if the structural rule changes.

Record significant decisions in:

docs/DECISIONS.md

**61\. Dependency Direction**

Prefer dependencies flowing toward business logic.

Conceptually:

Presentation

↓

Application

↓

Domain

↓

Infrastructure

The domain/business logic should not become tightly coupled to:

- Blade
- HTTP request objects
- external APIs
- specific UI components

For example:

ForecastService

should be usable from:

Controller

Job

Command

without knowing which one called it.

**62\. Avoid Over-Abstraction**

Do not introduce:

- repositories everywhere
- interfaces everywhere
- factories everywhere
- DTOs everywhere
- unnecessary domain layers

unless they solve an actual problem.

Laravel's Eloquent ORM and service/action patterns are sufficient for the initial architecture.

Prefer simple code over architectural ceremony.

**63\. Recommended Project Structure**

A reasonable initial structure:

app/

├── Actions/

│ ├── Inventory/

│ ├── Purchasing/

│ ├── Sales/

│ └── Forecasting/

│

├── Console/

│

├── Exceptions/

│

├── Http/

│ ├── Controllers/

│ ├── Requests/

│ └── Middleware/

│

├── Jobs/

│

├── Models/

│

├── Notifications/

│

├── Policies/

│

├── Services/

│ ├── Inventory/

│ ├── Forecasting/

│ ├── Replenishment/

│ ├── Notifications/

│ └── Reports/

│

└── Support/

This structure is a guideline, not an absolute requirement.

Use Laravel conventions when they provide a cleaner implementation.

**64\. Suggested Domain Organization**

As the project grows:

app/

├── Actions/

│ ├── Inventory/

│ ├── Forecasting/

│ ├── Purchasing/

│ └── Sales/

│

├── Services/

│ ├── Inventory/

│ ├── Forecasting/

│ ├── Replenishment/

│ ├── Notifications/

│ └── Reports/

│

└── Models/

├── Product.php

├── Category.php

├── Supplier.php

├── Warehouse.php

├── InventoryStock.php

├── InventoryTransaction.php

├── Purchase.php

├── PurchaseItem.php

├── Sale.php

├── SaleItem.php

└── DemandForecast.php

Do not create every class in advance.

Create classes when the corresponding feature is implemented.

**65\. Critical Architectural Invariants**

The following rules are considered architectural invariants.

**Invariant 1**

Every stock-changing operation must produce an inventory transaction.

**Invariant 2**

Inventory-changing operations must be atomic.

**Invariant 3**

Historical inventory transactions must remain auditable.

**Invariant 4**

Current inventory must remain consistent with inventory transactions.

**Invariant 5**

Forecasting must not directly modify inventory.

**Invariant 6**

Restock recommendation must not automatically purchase goods without explicit approval.

**Invariant 7**

Business-critical calculations must be testable independently from HTTP/UI.

**Invariant 8**

Authorization must be enforced server-side.

**Invariant 9**

External integrations must not be tightly coupled to core business logic.

**Invariant 10**

A feature is not complete without appropriate tests.

**66\. Typical Business Flow**

The complete system should eventually support:

PURCHASE

│

▼

STOCK IN

│

▼

INVENTORY STOCK

│

▼

STOCK LEDGER

│

│

▼

SALES

│

▼

STOCK OUT

│

▼

HISTORICAL DEMAND

│

▼

FORECASTING

│

┌────────────┼────────────┐

▼ ▼ ▼

Safety Stock ROP Stockout Days

│ │ │

└────────────┼────────────┘

▼

RESTOCK RECOMMENDATION

│

▼

PURCHASING REVIEW

**67\. Development Sequence**

Architecture should be implemented progressively.

Recommended order:

1\. Laravel foundation

2\. Authentication

3\. Master data

4\. Inventory state

5\. Inventory ledger

6\. Stock In

7\. Stock Out

8\. Stock Transfer

9\. Purchasing

10\. Sales

11\. Stock Card

12\. Dashboard

13\. Forecasting

14\. Safety Stock

15\. ROP

16\. Restock Recommendation

17\. Notifications

18\. Queue

19\. Reports

20\. Testing & hardening

21\. Deployment

Do not implement forecasting before inventory history is reliable.

**68\. Architecture Decision Rule**

When multiple technical solutions are possible:

Prefer the solution that:

1. satisfies the PRD
2. preserves data integrity
3. is easiest to test
4. follows Laravel conventions
5. has the fewest unnecessary dependencies
6. is understandable by another developer
7. can evolve without major rewrites

Do not choose a more sophisticated architecture merely because it is technically impressive.

**69\. Final Architecture Principle**

The system should follow this principle:

**Simple application structure, strong business rules, reliable inventory integrity, explainable forecasting, and incremental evolution.**

The architecture should make it difficult to accidentally corrupt inventory data while keeping the rest of the application simple enough to develop and maintain.
