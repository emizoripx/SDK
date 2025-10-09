# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [UNRELEASED] - 2025-10-09

### Added
- **Owner-Based Pattern**: Implemented owner-based pattern with `HasEmizorCredentials` trait for polymorphic credential association to models (Company, User)
- **Polymorphic Relations**: Added `morphTo` relation in `BeiAccount` and `owner_type`/`owner_id` fields for polymorphic support
- **EmizorManager Service**: New primary service `EmizorManager` for dynamic operations, replacing legacy logic
- **Credential Encryption**: Automatic encryption of `bei_client_secret` using Laravel's `encrypted` casts
- **Dynamic Manager Access**: `for($owner)` method in facade for dynamic manager access per owner
- **Homologation Methods**: Integrated `homologateProduct()` and `homologateProductList()` in `EmizorManager`
- **Defaults Methods**: Integrated `setDefaults()` and `getDefaults()` in `EmizorManager`

### Changed
- **Registration Process**: Updated to use `updateOrCreate` with `owner_type`/`owner_id`, enabling upsert per owner
- **Service Architecture**: Migrated from `EmizorApi` to `EmizorManager` using `EmizorApiService` directly for better efficiency
- **Facade Methods**: `register()` and `for($owner)` now use separate bindings for clarity
- **Database Schema**: `bei_client_secret` changed to `text` for encryption support; added composite index on `owner_type`/`owner_id`

### Removed
- **EmizorApi Class**: Removed `EmizorApi` class and `EmizorApiContract` to align with owner-based pattern
- **withAccount() Method**: Removed `withAccount()` from facade to avoid exposing `accountId`
- **Legacy Bindings**: Removed `EmizorApiContract` bindings in `ServiceProvider`

### Fixed
- **Test Compatibility**: Updated tests to use owner-based pattern and `for()` facade method
- **Migration Issues**: Fixed `bei_client_secret` field length for encrypted data

### Security
- **Credential Encryption**: Implemented encryption for sensitive credentials to enhance security

## [UNRELEASED] - 2025-09-29

### Added
- **Account Registration**: Implemented `register()` method to create new EMIZOR accounts with client credentials
- **Token Generation**: Added automatic token generation and management for API authentication
- **Parametric Synchronization**: Added `syncParametrics()` and `getParametric()` methods for syncing fiscal parametrics (activities, products, payment methods, etc.)
- **Defaults Configuration**: Implemented `setDefaults()` and `getDefaults()` for setting account-specific default values
- **Parametric Types Listing**: Added `listParametricTypes()` to retrieve available parametric types
- **Product Homologation**: Added `homologateProduct()` and `homologateProductList()` for product code homologation with fiscal entities
- **Invoice Management**: Implemented `issueInvoice()` for electronic invoice emission and `revocateInvoice()` for invoice revocation
- **NIT Validation**: Added `validateNit()` for taxpayer identification number validation
- **Database Migrations**: Created migrations for accounts, parametrics, products, and offline invoice tracking
- **Event System**: Added events for invoice status changes (accepted, rejected, in process, revocated, reverted)
- **Jobs and Scheduling**: Implemented background jobs for offline invoice tracking

### Changed
- **Registration API**: Refactored to use fluent builder pattern instead of DTO/array parameters
- **Parametric Sync**: Moved API connection logic to `EmizorApiService` for better separation of concerns
- **Jobs Enhancement**: Improved `SyncGlobalParametrics`, `SyncSpecificParametrics`, and `EnsureToken` jobs with better error handling, logging, and documentation
- **Automatic Post-Registration**: Account registration now automatically generates tokens and synchronizes both global and account-specific parametrics

### Fixed
- **EmissionResource**: Fixed hardcoded values, added null-safe access to client data, corrected discount calculation, and improved error handling
- **EmissionDetailsResource**: Replaced hardcoded SIN codes with dynamic values from resource data, added null-safe access

### Added
- **InvoiceEmissionService**: Implemented product details merging with homologated products to automatically populate SIN codes before emission
- **Validators and DTOs**: Added data validation and transfer objects for API interactions
- **Contracts and Interfaces**: Defined contracts for dependency injection and interface segregation

### Changed
- **Token Implementation**: Refactored token usage across services for better consistency
- **Parametric Service**: Updated parametric service instantiation in EmizorApi class
- **Parametric Types**: Improved validation and handling of parametric types
- **Validation Logic**: Enhanced parametric type validation rules

### Fixed
- Improved error handling and exception management
- Fixed dependency injection bindings in service provider 
