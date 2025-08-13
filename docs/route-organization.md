# Route Organization

This document explains how the application routes are organized for better maintainability and clarity.

## File Structure

```
routes/
├── web.php              # Main entry point, includes other route files
├── global.php           # Global routes (no tenant context)
├── global-settings.php  # Global user settings routes
├── admin.php           # System admin routes
├── tenant.php          # Tenant-scoped routes
├── settings.php        # Tenant-scoped settings routes
└── auth.php            # Tenant-scoped authentication routes
```

## Route Categories

### 1. Global Routes (`routes/global.php`)
Routes that are available globally without tenant context:
- Home page
- Authentication (login, register, password reset)
- Email verification
- Global dashboard
- Tenant selection

### 2. Global Settings (`routes/global-settings.php`)
User settings that are available outside of tenant context:
- Profile management (`/settings/profile`)
- Password management (`/settings/password`)
- Session management (`/settings/sessions`)
- Appearance settings (`/settings/appearance`)

### 3. System Admin Routes (`routes/admin.php`)
Routes only accessible to system administrators:
- Admin dashboard (`/admin/dashboard`)
- Tenant management (`/admin/tenants`)
- User management (`/admin/users`)

### 4. Tenant Routes (`routes/tenant.php`)
Routes that require tenant context (both subdomain and path-based):
- Tenant dashboard
- Project management
- Tenant-specific settings and authentication

## Route Naming Conventions

### Global Routes
- `login` - Global login page
- `profile.edit` - Global profile settings
- `admin.dashboard` - System admin dashboard

### Tenant Routes
- `dashboard` - Tenant dashboard (subdomain-based)
- `tenant.dashboard` - Tenant dashboard (path-based)

## Middleware Groups

### Global Context
```php
Route::middleware('auth')->group(function () {
    Route::middleware('verified')->group(function () {
        // Verified user routes
    });
});
```

### Tenant Context
```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Tenant-scoped routes
});
```

### System Admin
```php
Route::middleware('can:manage-platform')->group(function () {
    // System admin routes
});
```

## Benefits of This Organization

1. **Separation of Concerns**: Each file handles a specific type of route
2. **Maintainability**: Easier to find and modify specific route groups
3. **Clarity**: Clear distinction between global and tenant-scoped routes
4. **Scalability**: Easy to add new route categories as the application grows
5. **Testing**: Easier to test specific route groups in isolation

## Migration Notes

The refactoring maintains backward compatibility:
- All existing route names are preserved
- Tests continue to work without modification
- Frontend route helpers continue to work as expected

## Adding New Routes

### For Global Features
Add routes to `routes/global.php` or create a new file and include it in `global.php`.

### For Tenant Features
Add routes to `routes/tenant.php` or the appropriate tenant-scoped file.

### For Admin Features
Add routes to `routes/admin.php` with the `can:manage-platform` middleware.