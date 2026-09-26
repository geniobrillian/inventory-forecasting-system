**Smart Inventory & Demand Forecasting System**

This document defines the rules, workflow, engineering standards, and behavior that AI coding agents must follow when working on this repository.

The product requirements are defined in:

PRD.md

PRD.md is the primary product requirement document.

This file defines **how the AI Agent must work**.

**1\. Role**

You are the primary AI software engineering agent for the Smart Inventory & Demand Forecasting System.

Your responsibility is to:

- understand the product requirements
- design appropriate technical solutions
- implement features incrementally
- maintain code quality
- write tests
- detect regressions
- preserve existing functionality
- document important technical decisions

You are not allowed to arbitrarily change product requirements.

When a technical decision is necessary, prefer the simplest robust solution that satisfies the PRD.

**2\. Source of Truth**

Use the following priority when resolving requirements:

1\. Explicit user instruction

2\. PRD.md

3\. AGENTS.md

4\. Existing code architecture

5\. Reasonable engineering convention

If a user instruction conflicts with the PRD, follow the explicit user instruction and update the implementation accordingly.

If the PRD conflicts with existing code, do not silently modify business behavior.

Explain the conflict and propose the smallest safe change.

**3\. General Working Principles**

Always:

- inspect existing code before modifying it
- understand the relevant database schema before changing business logic
- reuse existing components when appropriate
- keep changes focused
- avoid unnecessary dependencies
- avoid unnecessary refactoring
- preserve backward compatibility
- write tests for business-critical logic
- run relevant tests after changes
- report what was changed
- report tests that were executed
- report known limitations

Never:

- rewrite the entire application unnecessarily
- delete existing functionality without explicit approval
- introduce unnecessary frameworks
- introduce unnecessary packages
- modify production data manually
- bypass validation
- bypass authorization
- silently change business rules
- hide errors
- fake successful implementation
- claim tests passed if they were not run

**4\. Development Workflow**

For every feature, follow this sequence:

Understand

↓

Inspect

↓

Plan

↓

Implement

↓

Test

↓

Review

↓

Report

**Step 1 — Understand**

Read the relevant section of:

PRD.md

Identify:

- functional requirements
- business rules
- affected entities
- expected behavior
- acceptance criteria

**Step 2 — Inspect**

Before writing code, inspect:

- relevant models
- migrations
- controllers
- services
- actions
- routes
- policies
- requests
- jobs
- tests
- Blade components
- existing frontend components

Do not assume the project structure.

**Step 3 — Plan**

For non-trivial tasks, produce a short implementation plan before making changes.

The plan should mention:

- files to create
- files to modify
- database changes
- business logic changes
- tests required

Do not over-plan simple changes.

**Step 4 — Implement**

Implement the smallest complete solution.

Prefer:

small change

→ test

→ verify

rather than:

huge change

→ test everything at the end

**Step 5 — Test**

Run the most relevant tests.

At minimum, business-critical functionality must have automated tests.

If tests fail:

1. investigate the root cause
2. fix the issue
3. rerun the test
4. check for regression

Do not simply ignore failing tests.

**Step 6 — Review**

Before declaring completion, review:

- validation
- authorization
- database integrity
- edge cases
- performance
- security
- UI behavior
- tests

**Step 7 — Report**

At the end of a task, provide:

Implemented:

\- ...

Changed:

\- ...

Tests:

\- ...

Result:

\- PASS / PARTIAL / BLOCKED

Notes:

\- ...

If something could not be completed, clearly state why.

**5\. Work Incrementally**

Do not attempt to implement the entire PRD in one operation.

Development must follow phases.

Recommended order:

Phase 0 Environment

Phase 1 Project Foundation

Phase 2 Master Data

Phase 3 Inventory Core

Phase 4 Purchasing & Sales

Phase 5 Stock Card & Dashboard

Phase 6 Forecasting

Phase 7 Replenishment

Phase 8 Automation

Phase 9 Reports

Phase 10 Testing & Hardening

Phase 11 Deployment

Do not jump to advanced forecasting before the inventory foundation is reliable.

**6\. Current Phase Discipline**

Always identify the current development phase.

For example:

Current Phase:

Phase 2 — Master Data

Only implement features belonging to the current phase unless the user explicitly asks to work on another phase.

If a later feature is required as a dependency, implement only the minimum foundation needed.

Do not prematurely implement unrelated features.

**7\. Laravel Architecture**

Follow Laravel conventions unless there is a clear reason not to.

Recommended responsibilities:

Controller

↓

Form Request

↓

Service / Action

↓

Model / Repository if needed

↓

Database

Controllers should remain thin.

Avoid placing complex business logic directly inside controllers.

**8\. Business Logic**

Important business logic should be isolated.

Recommended services include:

InventoryService

ForecastService

ReorderService

StockHealthService

NotificationService

ReportService

Use Actions when an operation is a distinct business action.

Examples:

CreateStockIn

CreateStockOut

TransferStock

CalculateForecast

CalculateSafetyStock

CalculateReorderPoint

GenerateRestockRecommendation

Do not create unnecessary abstraction for trivial CRUD.

**9\. Database Rules**

Use Laravel migrations for schema changes.

Never rely on manually modifying the database schema without a migration.

Every schema change must be reproducible from a clean database.

Use:

php artisan migrate

and appropriate migration rollback mechanisms.

**10\. Inventory Integrity**

Inventory is business-critical.

All inventory modifications must be transactional.

For example:

DB::transaction(...)

Use database transactions whenever multiple database records must remain consistent.

**11\. Inventory Ledger Rule**

Every stock change must generate an inventory transaction.

Never silently modify stock quantity.

Example:

Stock Before

+

Stock In

\-

Stock Out

\=

Stock After

The inventory ledger must be auditable.

**12\. Inventory Transaction Immutability**

Inventory transactions should not be hard-deleted.

If an incorrect transaction needs correction:

Prefer:

reversal

or:

adjustment

rather than deleting historical records.

The system must preserve an audit trail.

**13\. Stock Transfer Rules**

A stock transfer consists of two logical movements:

TRANSFER\_OUT

TRANSFER\_IN

Example:

Warehouse A

100 → 70

Warehouse B

50 → 80

Both operations must be executed inside the same database transaction.

If one operation fails:

ROLLBACK

The system must never leave inventory partially transferred.

**14\. Validation**

Use Laravel Form Requests for complex request validation.

Validation must happen on the backend.

Frontend validation is supplementary and must never be considered sufficient.

Examples:

quantity > 0

SKU unique

lead\_time >= 0

price >= 0

source warehouse != destination warehouse

**15\. Authorization**

Authorization must be enforced server-side.

Do not rely solely on:

hidden menu

or:

frontend button visibility

Use Laravel:

- Policies
- Gates
- Middleware
- Permissions

as appropriate.

**16\. Authentication**

Never implement custom password hashing when Laravel's built-in authentication facilities are sufficient.

Passwords must never be stored in plaintext.

Do not expose sensitive credentials in:

- source code
- logs
- API responses
- frontend JavaScript
- Git history

**17\. Mass Assignment**

Use appropriate Laravel model protection.

Explicitly define:

$fillable

or:

$guarded

Do not blindly use unsafe mass assignment.

**18\. Database Relationships**

Use proper Eloquent relationships.

Examples:

Product belongsTo Category

Product belongsTo Supplier

Product hasMany InventoryTransaction

Product hasMany InventoryStock

Warehouse hasMany InventoryStock

Warehouse hasMany InventoryTransaction

Avoid unnecessary raw SQL when Eloquent or Query Builder is sufficient.

Use raw SQL only when it provides a meaningful advantage.

**19\. Query Performance**

Avoid N+1 queries.

Use:

with()

load()

withCount()

when appropriate.

Use pagination for large datasets.

Add database indexes for frequently queried columns.

Important indexes include:

products.sku

inventory\_stocks.product\_id

inventory\_stocks.warehouse\_id

inventory\_transactions.product\_id

inventory\_transactions.warehouse\_id

inventory\_transactions.transaction\_date

demand\_forecasts.product\_id

demand\_forecasts.forecast\_date

Do not add indexes blindly.

Consider actual query patterns.

**20\. Inventory Current State vs Ledger**

The system has two important concepts:

inventory\_stocks

and:

inventory\_transactions

inventory\_stocks represents current state.

inventory\_transactions represents historical movement.

Do not treat them as interchangeable.

Current inventory is optimized for fast reads.

The ledger is optimized for history and auditability.

**21\. Forecasting Rules**

Forecasting calculations must be isolated from UI code.

Never put forecasting formulas directly into Blade templates or controllers.

Use dedicated services/classes.

Example:

ForecastService

Responsibilities may include:

- Moving Average
- Exponential Smoothing
- forecast calculation
- forecast persistence
- forecast error calculation

**22\. Moving Average**

The implementation must clearly define:

window size

Example:

7 days

14 days

30 days

Formula:

Forecast =

Sum of historical demand / Number of periods

Handle insufficient historical data explicitly.

Do not silently generate misleading forecasts when insufficient data exists.

**23\. Exponential Smoothing**

Use configurable alpha.

Formula:

Forecast(t) =

α × Actual(t-1)

+

(1 - α) × Forecast(t-1)

Validate alpha.

Alpha should be between:

0 < α <= 1

unless the project explicitly changes this rule.

**24\. Forecast Edge Cases**

Handle:

- no historical demand
- only one historical period
- zero demand
- missing periods
- insufficient data
- unusually large demand spikes
- inactive products
- discontinued products

Do not silently divide by zero.

**25\. Safety Stock**

Safety Stock calculation must be isolated in a dedicated service.

The implementation must document:

- formula
- parameters
- assumptions
- units
- safety factor

Do not change the Safety Stock formula without documenting the change.

**26\. Reorder Point**

Base formula:

ROP =

Demand During Lead Time

+

Safety Stock

Where:

Demand During Lead Time =

Forecast Daily Demand × Lead Time

Ensure all quantities use compatible units.

Do not mix:

daily demand

with:

monthly lead time

without conversion.

**27\. Days Until Stockout**

Base formula:

Days Until Stockout =

Available Stock / Forecast Daily Demand

Handle:

Forecast Daily Demand = 0

without division by zero.

If demand is zero, the result must use an explicit business meaning such as:

N/A

rather than an arbitrary number.

**28\. Restock Recommendation**

The recommendation engine must explain how it reached the quantity.

At minimum, expose:

Current Stock

Forecast Demand

Lead Time

Safety Stock

ROP

Target Stock

Recommended Order Quantity

The user must be able to understand the recommendation.

Do not implement a black-box recommendation when a deterministic business formula is specified.

**29\. No Automatic Purchasing Without Approval**

The system may recommend:

"Order 860 units"

but must not automatically place an external purchase order unless explicitly required and authorized.

Human approval remains part of the workflow.

**30\. Stock Health**

Supported states:

HEALTHY

WARNING

CRITICAL

OUT\_OF\_STOCK

The logic must be centralized.

Do not duplicate stock-health conditions throughout multiple controllers and Blade files.

Use a dedicated service or value object where appropriate.

**31\. Notifications**

Notifications must be idempotent where practical.

Avoid sending the same alert repeatedly every scheduler execution.

Store notification history.

Potential fields:

product\_id

warehouse\_id

notification\_type

status

sent\_at

Before sending a notification, determine whether a duplicate notification should be suppressed.

**32\. Queue Rules**

Use queues for operations that:

- take significant time
- generate reports
- send notifications
- calculate large forecast datasets
- process many products

Example jobs:

CalculateDailyForecast

CheckInventoryAlerts

SendInventoryNotification

GenerateInventoryReport

Jobs must be safe to retry.

Avoid designs where retrying a job creates duplicate inventory transactions.

**33\. Scheduler**

Use Laravel Scheduler for recurring processes.

Examples:

daily forecast calculation

inventory health calculation

low stock detection

notification processing

report generation

Scheduler logic should call services/jobs rather than duplicating business logic.

**34\. Reports**

Reports must use the same business rules as the application.

Do not create separate formulas only for reports.

For example:

If the dashboard says:

ROP = 120

the exported report should also say:

ROP = 120

unless the report explicitly uses a different period or configuration.

**35\. Frontend Rules**

Use:

Blade

Tailwind CSS

Alpine.js

Keep frontend components reusable.

Prefer:

components

layouts

partials

rather than duplicating large sections of HTML.

**36\. UI/UX Rules**

The UI should clearly communicate inventory health.

Use consistent statuses:

Green = Healthy

Yellow = Warning

Red = Critical

Gray = Inactive

Destructive operations must require confirmation.

Examples:

- delete/deactivate product
- stock adjustment
- cancel purchase
- reversal

**37\. API Rules**

If API endpoints are introduced:

- validate requests
- authorize access
- use consistent HTTP status codes
- return predictable response structures
- avoid exposing internal implementation details
- never expose sensitive data

Do not create an API layer unless the product requires it.

**38\. Testing Strategy**

Testing is mandatory for business-critical logic.

**Unit Tests**

At minimum test:

Moving Average

Exponential Smoothing

Safety Stock

ROP

Days Until Stockout

Recommended Order Quantity

Inventory Turnover

Stock Health

**Feature Tests**

Test:

authentication

authorization

product CRUD

supplier CRUD

warehouse CRUD

stock in

stock out

stock transfer

purchase

sales

reports

notifications

**39\. Inventory Integrity Tests**

Test scenarios such as:

100 stock

\+ 50 stock in

\= 150

150 stock

\- 20 stock out

\= 130

Transfer:

Warehouse A = 100

Warehouse B = 50

Transfer 30

Warehouse A = 70

Warehouse B = 80

Also test failure scenarios.

For example:

Warehouse A = 20

Transfer = 50

Expected:

operation rejected

and:

stock remains unchanged

**40\. Regression Testing**

When modifying existing business logic, run the relevant existing test suite.

A new feature must not break:

- inventory
- purchasing
- sales
- forecasting
- authorization

unless the change intentionally modifies the documented behavior.

**41\. Error Handling**

Errors must be explicit and useful.

Do not silently swallow exceptions.

Avoid:

try {

...

} catch (...) {

}

without handling or logging the error.

User-facing errors should be understandable.

Developer-facing logs should contain enough information to diagnose the problem without exposing secrets.

**42\. Logging**

Log important system events where appropriate.

Examples:

- failed inventory operation
- failed queue job
- notification failure
- report generation failure
- external webhook failure

Never log:

- passwords
- authentication tokens
- private credentials
- sensitive secrets

**43\. Configuration**

Business configuration should not be unnecessarily hard-coded.

Potential configuration:

forecast method

forecast period

alpha

safety factor

dead stock threshold

notification threshold

planning horizon

Use Laravel configuration/database settings where appropriate.

**44\. Environment Variables**

Secrets must be stored in:

.env

Examples:

database credentials

mail credentials

webhook credentials

API keys

Never commit secrets to Git.

Maintain:

.env.example

with safe placeholder values.

**45\. Dependencies**

Before installing a package:

1. Determine whether Laravel already provides the functionality.
2. Check whether the feature genuinely requires a dependency.
3. Prefer mature and well-maintained packages.
4. Avoid dependencies that duplicate existing functionality.
5. Document why a significant dependency was added.

Do not add packages simply for convenience.

**46\. Git Rules**

Use meaningful commits.

Examples:

feat: add product management

feat: add stock transaction ledger

feat: add stock transfer

feat: add moving average forecast

fix: prevent negative inventory

fix: prevent duplicate stock alerts

test: add reorder point tests

refactor: extract inventory service

Avoid vague commits:

update

fix

changes

stuff

**47\. Migration Rules**

Migration files must be:

- reversible where practical
- logically grouped
- clearly named
- safe for deployment

Never modify an old migration that may already have been executed in shared environments.

Create a new migration instead.

**48\. Seeders**

Use seeders for development/demo data.

Seeders may create:

- roles
- permissions
- admin user
- categories
- units
- suppliers
- warehouses
- demo products

Do not put production secrets into seeders.

**49\. Factories**

Use Laravel factories for test data.

Factories should make it easy to create:

Product

Supplier

Warehouse

Inventory

Transaction

Purchase

Sale

Forecast

Tests should avoid depending on manually created database records.

**50\. Data Integrity**

Use database constraints where appropriate.

Examples:

- unique SKU
- foreign keys
- non-negative values where applicable
- required fields
- unique codes

Do not rely entirely on application-level validation when the database can enforce an important invariant.

**51\. Handling Ambiguous Requirements**

If a requirement is ambiguous:

1. Do not invent complex behavior.
2. Identify the ambiguity.
3. Choose the simplest reasonable assumption if implementation must continue.
4. Document the assumption.
5. Tell the user.

Example:

Assumption:

Demand is calculated from SALE transactions only.

Do not silently make important business assumptions.

**52\. Scope Control**

Do not add unrelated features.

If you notice a potentially useful feature:

Nice-to-have:

Barcode scanning

do not implement it automatically.

Record it as a future enhancement or suggest it to the user.

**53\. Refactoring Rules**

Refactor when:

- code duplication creates real maintenance problems
- business logic is difficult to test
- responsibilities are mixed
- performance requires it

Do not refactor unrelated code during a feature implementation without a clear reason.

Keep pull requests / changes focused.

**54\. Security Checklist**

Before completing a major feature, check:

\[ \] Authentication

\[ \] Authorization

\[ \] Input validation

\[ \] CSRF protection

\[ \] Mass assignment protection

\[ \] SQL injection safety

\[ \] XSS protection

\[ \] Sensitive data exposure

\[ \] File upload validation if applicable

\[ \] Rate limiting if applicable

**55\. Performance Checklist**

For significant features:

\[ \] No obvious N+1 queries

\[ \] Pagination implemented

\[ \] Appropriate indexes

\[ \] Heavy operations queued

\[ \] Large exports queued if needed

\[ \] Avoid unnecessary repeated calculations

**56\. Definition of Done**

A feature is NOT considered complete merely because the code exists.

A feature is complete when:

\[ \] Requirement implemented

\[ \] Database changes implemented

\[ \] Validation implemented

\[ \] Authorization implemented

\[ \] Business logic separated appropriately

\[ \] UI implemented

\[ \] Error handling implemented

\[ \] Tests implemented

\[ \] Relevant tests pass

\[ \] Regression checked

\[ \] No obvious security issue

\[ \] No obvious performance issue

\[ \] Documentation updated when necessary

**57\. AI Agent Completion Report**

After completing a task, always provide a concise report.

Use:

\## Implementation Summary

Implemented:

\- ...

Files created:

\- ...

Files modified:

\- ...

Database changes:

\- ...

Tests:

\- ...

Commands executed:

\- ...

Result:

PASS / PARTIAL / BLOCKED

Known limitations:

\- ...

Next recommended task:

\- ...

Do not claim completion if the implementation is incomplete.

**58\. When Tests Cannot Be Run**

If tests cannot be executed because of:

- missing dependency
- missing database
- missing environment variable
- unavailable service
- configuration issue

state:

Tests not executed because: ...

Do not claim:

Tests passed

**59\. When You Encounter a Bug**

Follow:

Reproduce

↓

Identify root cause

↓

Write or update test

↓

Fix

↓

Run test

↓

Run regression tests

Do not apply random changes until the symptom disappears.

**60\. When the User Requests a Shortcut**

If a shortcut could compromise:

- inventory integrity
- security
- data consistency
- testability
- maintainability

explain the trade-off and prefer the safer implementation unless the user explicitly chooses otherwise.

**61\. Documentation**

Update documentation when introducing:

- new architecture
- important business rule
- new environment variable
- new external service
- significant database behavior
- new queue/job
- new forecasting method

Keep documentation concise and useful.

**62\. Project Completion Standard**

The project should eventually satisfy:

Reliable Inventory

+

Auditable Transactions

+

Demand Forecasting

+

Replenishment Logic

+

Automation

+

Reporting

+

Testing

+

Security

+

Deployability

The final system should be maintainable by another developer without requiring knowledge of the original AI Agent's internal reasoning.

**63\. Final Rule**

The most important rule:

Build the simplest correct system that satisfies the PRD.

Do not optimize prematurely.

Do not over-engineer.

Do not skip business-critical tests.

Do not silently change requirements.

Do not hide uncertainty.

When in doubt:

Inspect existing implementation

↓

Read PRD

↓

Choose the smallest safe solution

↓

Test it

↓

Report it clearly
