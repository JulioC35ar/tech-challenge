# Tech Challenge Documentation

This repository contains the implementation of a maintenance order management system (CMMS) built with Laravel 12 and Filament 3, developed in response to the main requirements of a technical challenge. In addition to the requested features, some enhancements were included to enrich the user experience and demonstrate extra attention to detail.

### No additional setup is required to run the project.

## Technologies Used

- Laravel 12
- FilamentPHP 3
- PHP 8.4
- PestPHP
- SQLite

## Key Features

### Roles and Access

- **Supervisors:** Can create, view, edit, approve, or reject maintenance orders.
- **Technicians:** Can view and manage assigned orders.

### Maintenance Order Module

- Full CRUD for maintenance orders.
- Filters by status, priority, and technician (technician filter available only for supervisors).
- Comment system included with its own module.
- To improve the user experience and streamline multiple actions, quick-access buttons were added to the maintenance orders list.

### Extra Modules
- Assets and Users have their own modules.
- Extra modules like comments, users and asset management were added mainly for supervisors, who manage and oversee maintenance tasks.

### Dashboard

- Total orders by priority displayed in cards.
- Bar chart by order status.
- Pie chart showing assigned orders by technician.

### Testing

- Basic Tests written with Pest to validate:
  - Permissions for creating and viewing orders.

## Running Tests

```bash
./vendor/bin/pest
```

This project was developed as part of a technical challenge and is ready for evaluation. Feel free to reach out in case of questions or feedback.

Thanks for reviewing the repository

