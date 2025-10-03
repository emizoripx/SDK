# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
