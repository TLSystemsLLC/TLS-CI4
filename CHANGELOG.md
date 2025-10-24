# Changelog - TLS-CI4

All notable changes to the TLS-CI4 CodeIgniter 4 migration project.

## [Unreleased]

### Added - 2025-10-24

#### Base Entity Template System (Phase 7)
**MAJOR FEATURE:** Complete reusable template system for all entity maintenance screens.

**Core Template (write once, use forever):**
- `app/Controllers/BaseEntityMaintenance.php` (707 lines) - Abstract controller with all 15 standard endpoints
- `app/Views/partials/entity_search.php` (60 lines) - Reusable search section
- `app/Views/partials/form_field_renderer.php` (90 lines) - Auto-generates form fields from definitions
- `app/Views/partials/entity_address.php` (70 lines) - Reusable address management
- `app/Views/partials/entity_contacts.php` (80 lines) - Reusable contact management
- `app/Views/partials/entity_comments.php` (60 lines) - Reusable comment management
- `app/Views/safety/base_entity_maintenance.php` (140 lines) - Base view template
- `public/js/tls-entity-maintenance.js` (640 lines) - Generic JavaScript for all entities

**Example Implementation:**
- `app/Controllers/DriverMaintenance_NEW.php` (442 lines) - Driver using base template
- `app/Views/safety/driver_maintenance_template.php` (15 lines) - Driver view wrapper

**Documentation:**
- `BASE_ENTITY_TEMPLATE_DESIGN.md` - Complete design documentation
- `BASE_TEMPLATE_PROGRESS.md` - Implementation progress tracking
- `BASE_TEMPLATE_COMPLETE.md` - Final summary and usage guide
- `LESSONS_LEARNED.md` - Lessons from Driver Maintenance initial implementation

**Benefits:**
- 78% code reduction per entity (457 lines vs 2,055 lines)
- Zero find/replace needed for new entities
- Guaranteed consistency across all entities
- Field-driven form generation
- Bug fixes propagate automatically
- New entity creation in 15-30 minutes vs 2-4 hours

**Standard Endpoints (all entities inherit):**
1. `index()` - Display page
2. `search()` - Search by key/name
3. `autocomplete()` - Autocomplete dropdown
4. `save()` - Save entity (abstract - child implements)
5. `load($key)` - Load entity by key
6. `createNew()` - Create new entity
7. `getAddress()` - Get entity address
8. `saveAddress()` - Save entity address
9. `getContacts()` - Get entity contacts
10. `saveContact()` - Save contact
11. `deleteContact()` - Delete contact
12. `getContactFunctionOptions()` - Get contact function dropdown options
13. `getComments()` - Get entity comments
14. `saveComment()` - Save comment
15. `deleteComment()` - Delete comment

---

## [0.6.0] - 2025-10-23

### Added

#### Agent Maintenance (Phase 6) - COMPLETE
- Complete entity maintenance pattern with address, contact, and comment management
- Serves as original template (superseded by Base Entity Template System in Phase 7)

#### Company & Division Maintenance (Phase 5.5)
- Full CRUD for Departments and Teams
- Multi-level organizational hierarchy

#### User Security (Phase 5)
- Menu permission management
- Category-level toggles
- Role templates

#### User Maintenance (Phase 4)
- First entity maintenance screen
- TLSAutocomplete component
- TLSFormTracker for change tracking

---

## [0.3.0] - 2025-10-21

### Added

#### MenuManager Migration (Phase 3)
- Pure MVC architecture
- Session-based permission caching
- Responsive mobile menu

---

## [0.2.0] - 2025-10-18

### Added

#### Authentication Infrastructure (Phase 2)
- BaseModel with stored procedure helpers
- TLSAuth library with three-field authentication
- AuthFilter middleware
- BaseController with automatic database context switching
- Multi-tenant isolation

---

## [0.1.0] - 2025-10-15

### Added

#### Foundation Setup (Phase 1)
- CodeIgniter 4.6.3 installed via Composer
- SQL Server SQLSRV driver configured
- MAMP development environment
- TLS custom UI theme
- Clean URLs configured
- GitHub repository created

---

## Version Guidelines

- **Major version (X.0.0):** Breaking changes, major architecture changes
- **Minor version (0.X.0):** New features, new phases complete
- **Patch version (0.0.X):** Bug fixes, minor improvements

## Semantic Versioning

This project follows [Semantic Versioning](https://semver.org/):
- Version 1.0.0 will be released when all core entity maintenance screens are migrated
- Until then, 0.x.x versions indicate work in progress
