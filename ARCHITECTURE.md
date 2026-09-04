# CourseGrid Architecture

## Hybrid Architecture Approach

CourseGrid is built using a hybrid architecture that serves two distinct frontends from a unified backend logic core:

1.  **Inertia.js Web Frontend:** The primary web application uses React components served directly by Laravel via Inertia.js. This provides a fast, SPA-like experience without the complexity of a fully separated client application.
2.  **JSON API:** A future mobile application and potential third-party integrations will consume a standard JSON REST API.

To avoid duplicating business logic across these two presentation layers, we strictly adhere to a **Service Pattern** and **Repository Pattern** architecture.

### 1. Controllers (The Presentation Layer)
Controllers in CourseGrid must remain exceptionally thin. Their responsibilities are limited to:
*   Receiving HTTP requests.
*   Validating request data (using FormRequests).
*   Calling the appropriate Service method(s).
*   Returning the appropriate response format (Inertia Response or JSON Response).

We maintain separate controllers for the Web and API layers:
*   `App\Http\Controllers\` (Inertia Web Controllers)
*   `App\Http\Controllers\Api\V1\` (JSON API Controllers)

**Example:**
Both `\App\Http\Controllers\CourseController` and `\App\Http\Controllers\Api\V1\CourseController` inject `\App\Services\CourseService`. The web controller passes the resulting data to `Inertia::render()`, while the API controller returns it wrapped in a `JsonResponse` (or an Eloquent API Resource).

### 2. Services (The Business Logic Layer)
All business logic, orchestrations, third-party API calls, and complex data transformations live in the `app/Services/` directory. Services are independent of the HTTP context (they do not know about Requests or Responses). This makes them highly testable and reusable across the web controllers, API controllers, Console Commands, and Queue Jobs.

### 3. Financial Pipeline and Idempotency
The system processes complex money operations involving payments, instructor revenue splits, and subscriptions. Because of the critical nature of these operations, the architecture strictly enforces:
*   **Idempotency Keys:** Important entities (like `PaymentAttempts` and `Earnings`) require unique idempotency keys that are protected by database-level `UNIQUE` constraints.
*   **Database Transactions:** Business logic that touches multiple financial records is wrapped in `DB::transaction()`.
*   **Conflict Resolution:** Explicit catching of database unique constraint violations (MySQL Error 1062 / SQLSTATE 23000) prevents race conditions and duplicate financial entries without suppressing unrelated query errors.
