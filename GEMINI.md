# CORE WORKFLOW RULES
1. MANDATORY PLANNING: Before writing or modifying any code for a new feature or bug fix, you MUST output a brief plan detailing which files will be created/modified and wait for Hedra's approval (e.g., "Here is the plan... Shall I proceed?").
2. CONTEXT GATHERING: Always read `AI_PROJECT_CONTEXT.md` (if available) before answering architecture questions.

# LARAVEL BOOST & MCP USAGE (CRITICAL)
3. NO SCHEMA GUESSING: You must use the database inspection tool to read the actual database schema before writing Eloquent queries, migrations, or relationships.
4. NO LOG GUESSING: If an error is reported, DO NOT guess the solution. Use the Log Reader tool to inspect the latest Laravel/server logs first.
5. VERIFY WITH TINKER: Whenever applicable, test complex logic or queries via the Tinker integration before providing the final code block.
6. RTFM: If you are unsure about syntax for Laravel, Livewire, Inertia, or Spatie packages, use the Documentation Search tool via Boost. Do not use deprecated code.

# LARAVEL CODING STANDARDS
7. CONTROLLERS: Keep controllers strictly for routing HTTP requests. NO business logic in controllers. 
8. SERVICES & ACTIONS: Put core business logic in dedicated Service classes or Single-Action classes.
9. VALIDATION: Always use Form Requests for validation. Never validate inside the controller method.
10. DATABASE PERFORMANCE: Strictly avoid N+1 queries. Always use `with()` for eager loading when retrieving related models. Use DB Transactions for multi-step data manipulations.
11. CLEAN CODE: Follow PSR-12 coding standards. Ensure variable and method names are descriptive (e.g., `calculateTotalInvoiceAmount()` instead of `calcTotal()`).

# DESTRUCTIVE ACTIONS PROTOCOL
12. NEVER drop database tables, delete existing columns, or delete critical files without explicit, uppercase permission from Hedra (e.g., "YES, DELETE IT").