# Current Features (as of January 17, 2026)

## Core Functionality
- **Custom Login Page**: Users log in with username or email (custom Vue 3 form + Laravel backend).
- **Dashboard**: Displays financial overview with stat cards and charts.
- **Expense Management**: Full CRUD (Create, Read, Update, Delete) for expenses with pagination, sorting, and filtering.
- **Income Management**: Full CRUD for income entries with pagination, sorting, and filtering.
- **Category Management**: Create, edit, and delete custom expense/income categories in Settings.
- **Calendar View**: Calendar interface for viewing transactions by date.
- **Reports**: Financial reports with charts and analytics (in progress).

## UI/UX
- **Light/Dark Mode Toggle**: Theme switching with persistent storage.
- **Responsive Design**: Mobile-friendly layout with collapsible sidebar.
- **Pagination Controls**: Dropdown and prev/next buttons for data tables.
- **Icons**: SVG icons for actions (add, delete, refresh, collapse/expand).
- **Color-coded Categories**: Expense and income categories with visual badges.
- **Flash Messages**: Toast notifications for success/error feedback.

## Database & Backend
- **User Roles & Auth**: User registration, login, logout, and session management.
- **Expense Table**: Stores transaction date, name, category, amount, balance.
- **Income Table**: Stores transaction date, name, category, amount, balance.
- **Category Tables**: User-scoped expense and income categories with soft deletes.
- **Default Categories**: Food, Transport, Utilities for expenses; Salary, Interest for income.

## API Endpoints
- `POST /register` - User registration
- `POST /login` - User login
- `POST /logout` - User logout
- `GET/POST /expense` - List and create expenses
- `PATCH/DELETE /expense/{id}` - Update and delete expenses
- `GET/POST /income` - List and create income
- `PATCH/DELETE /income/{id}` - Update and delete income
- `GET /categories` - List categories by type
- `POST /categories` - Create category
- `PATCH /categories/{id}` - Update category
- `DELETE /categories/{id}` - Delete category

## Testing
- **Cypress E2E Tests**: Full workflow tests for login, expense/income add, bulk delete, and category management.
- **PHPUnit Unit Tests**: Backend validation and logic tests.

## Not Yet Implemented
- **Registration Frontend**: Form UI exists but backend integration incomplete.
- **Forgot Password**: UI link present, but no backend logic.
- **Email Verification**: Not yet implemented.
- **Export/Import**: Data export (CSV, PDF) not yet available.
- **Recurring Transactions**: Scheduled/recurring expenses and income.
- **Budget Management**: Setting and tracking budgets.
- **Advanced Analytics**: ML-based spending predictions, anomaly detection.

---

For planned features or to report issues, see the project board or contact the maintainer.

