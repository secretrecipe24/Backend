# Wellness System Architecture

This architecture is aligned with the current project structure and implemented API modules.

## 1) Architecture Style

The system follows a **3-tier web architecture** with clear separation of concerns:

- **Presentation Layer**: React + Vite frontend (`frontend/`)
- **Application Layer**: Laravel REST API (`backend/`)
- **Data Layer**: Relational database via Laravel Eloquent + Migrations

It also applies **role-based access control** and **token-based authentication** using Laravel Sanctum.

## 2) High-Level Logical Architecture

```mermaid
flowchart LR
    subgraph Presentation[Presentation Layer]
        UI1[Client Portal - React]
        UI2[Coach Portal - React]
        UI3[Admin Portal - React]
    end

    subgraph App[Application Layer - Laravel API]
        R[API Routes]
        MW[Middleware\nauth:sanctum, role guards, subscription]
        C1[Auth Controllers]
        C2[Subscription & Payment Controllers]
        C3[Matchmaking Controller]
        C4[Coach Plan/Monitoring Controllers]
        C5[Client Workout/Meal/Progress Controllers]
        C6[Admin Management Controllers]
        M[Domain Models / Services]
    end

    subgraph Data[Data Layer]
        DB[(MySQL Database)]
        FS[(File Storage\nprogress photos, payment screenshots)]
    end

    UI1 -->|HTTPS JSON| R
    UI2 -->|HTTPS JSON| R
    UI3 -->|HTTPS JSON| R

    R --> MW
    MW --> C1
    MW --> C2
    MW --> C3
    MW --> C4
    MW --> C5
    MW --> C6

    C1 --> M
    C2 --> M
    C3 --> M
    C4 --> M
    C5 --> M
    C6 --> M

    M --> DB
    C2 --> FS
    C5 --> FS
```

## 3) Deployment / Runtime Architecture

```mermaid
flowchart TB
    U[User Browser] --> FE[Frontend App\nReact + Vite Build]
    FE --> API[Laravel API Server\nPHP 8.2]

    API --> AUTH[Sanctum Token Auth]
    API --> RBAC[Role Middleware\nuser / coach / admin]
    API --> SUBGATE[Subscription Middleware]

    API --> DB[(Relational Database)]
    API --> STORAGE[(Local/Public Storage)]
```

## 4) Core Module Architecture

```mermaid
flowchart LR
    subgraph ClientDomain[Client Domain]
        CD1[Subscription]
        CD2[Matching]
        CD3[Workout & Meal Logging]
        CD4[Progress Tracking]
    end

    subgraph CoachDomain[Coach Domain]
        CO1[Match Response]
        CO2[Plan Authoring]
        CO3[Client Monitoring]
    end

    subgraph AdminDomain[Admin Domain]
        AD1[Payment Verification]
        AD2[User/Coach Management]
        AD3[Content Management]
        AD4[Reports & Exports]
    end

    CD1 --> AD1
    CD2 --> CO1
    CO2 --> CD3
    CD4 --> CO3
    AD2 --> CO1
    AD3 --> CO2
    AD4 --> AD1
```

## 5) Key Request Flow (End-to-End)

### A. Premium Client Journey
1. Client authenticates (`auth:sanctum`).
2. Client submits subscription payment.
3. Admin verifies payment and activates subscription.
4. Subscription middleware unlocks premium routes.
5. Client sets preferences, gets coach suggestions, selects a coach.
6. Coach accepts match and publishes workout/meal plans.
7. Client logs activities and submits progress updates.
8. Coach monitors adherence and adds coaching notes.

### B. Security and Access Controls
- **Authentication**: token-based with Sanctum.
- **Authorization**: route middleware for `user`, `coach`, and `admin` areas.
- **Feature gating**: `subscription` middleware for premium-only modules.
- **Audit-sensitive actions**: payment verification and admin actions stored in DB.

## 6) Data Architecture Summary

- **Identity and role data**: `users`, `coaches`, `admins`, `personal_access_tokens`
- **Commercial data**: `subscriptions`, `payments`
- **Coaching data**: `user_preferences`, `matches`, `workout_plans`, `meal_plans`
- **Activity and outcomes**: `workout_logs`, `meal_logs`, `measurements`, `progress_photos`, `coach_notes`
- **Reference content**: `exercises`, `foods`, `generic_content`

## 7) Non-Functional Considerations

- **Scalability**: stateless API design supports horizontal scaling at app tier.
- **Maintainability**: role-separated controller modules and migration-driven schema changes.
- **Extensibility**: architecture can add notifications/chat/analytics as new bounded modules.
- **Observability**: Laravel logging and request middleware chain support operational tracing.

## 8) Related Documents

- [ERD.md](ERD.md)
- [USE_CASE_DIAGRAM.md](USE_CASE_DIAGRAM.md)
- [DATA_FLOW_DIAGRAM.md](DATA_FLOW_DIAGRAM.md)
