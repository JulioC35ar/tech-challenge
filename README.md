# Documentation

Documentation are available at [DOCUMENTATION.md](./DOCUMENTATION.md).

# Technical Challenge: Oxygen CMMS

## Stack

This challenge must be solved using [**Laravel 12**](https://laravel.com/) and [**Filament 3**](https://filamentphp.com/). The interface and resource management should be implemented using Filament.

## General Context

In a CMMS (Computerized Maintenance Management System), maintenance orders are requests to repair, inspect, or intervene on assets within a facility.

These orders must be:

- Created by supervisors who detect a problem on an asset.
- Executed and closed by technicians once the intervention is completed.
- Approved or rejected by supervisors after execution to validate the closure.

The system must ensure an orderly workflow, controlled by states and access restrictions according to the user's role.

## Maintenance Order Statuses

1. **Created**  
   Initial state when the order is created (by the supervisor).

2. **In progress**  
   When a technician starts working on the order.

3. **Pending approval**  
   When the technician finishes the task and marks it as ready for review.

4. **Approved / Rejected**  
   Final outcome decided by a supervisor after reviewing the work.

## Technical Requirements

You can model and implement the **Maintenance Order** (`MaintenanceOrder`) resource as you want. But minimum information must be:

- Title.
- Related Asset.
- Status.
- Priority.
- Assigned Technician.
- Rejection reason (if applies).

## Use Case

### Supervisor

- Can **create** new orders, **assign** them to a technician, vinculate with an asset, and set a priority (`high`, `medium` or `low`).
- Can **approve or reject** orders in `Pending approval` status.
- **Cannot** change the status of an order to `In progress`.

### Technician

- Can **only view** the orders assigned to them.
- Can **start an order** (`In progress`) when beginning the task.
- Upon completion, must **mark the order as `Pending approval`**.
- Orders should be displayed **sorted by priority** (from high to low).
## Getting Started

To begin the challenge, follow these steps:

1. **Fork the repository**

   Go to `https://github.com/OxygenCL/tech-challenge` and click the **"Fork"** button in the top-right corner to create your own copy of the repository.

2. **Clone your forked repository**

   ```bash
   git clone https://github.com/your-username/tech-challenge.git
   cd tech-challenge
   ```

3. **Install dependencies**

   ```bash
   composer install
   npm install && npm run dev
   ```

4. **Set up the environment**

   Copy the `.env.example` to `.env`. Use SQLite as database provider.

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run migrations and seeders**

   ```bash
   php artisan migrate --seed # seeding the database creates 3 users and 5 assets
   ```

6. **Start the local server**

   ```bash
   php artisan serve
   ```

7. **Access the system**

   Visit `http://127.0.0.1:8000/admin` in your browser (or whatever is your local host)

   Use one of the following users to log in:

   - `super@oxygen.test` / password: `sup-pass`
   - `tech1@oxygen.test` / password: `tech1-pass`
   - `tech2@oxygen.test` / password: `tech2-pass`

> **Note:** Filament is already installed and configured in the project.

---

## Submission Instructions

Once you have completed the challenge:

1. Push your code to your **public GitHub fork**.
2. Make sure the latest commit reflects your final implementation.
3. Share the repository URL by email:
   - **To**: Oscar Carvajal (TL) `<ocarvajal@oxygen.tech>`
   - **CC**: Camilo Duque (SWE) `<cduque@oxygen.tech>` and Diego Torres (PM) `<dtorres@oxygen.tech>`

> ⚠️ Please ensure that the repository is publicly accessible. Private repositories or ZIP file submissions will not be accepted.

_Enjoy and good luck!_ :rocket: