# Wellness System Data Flow Diagram (DFD)

This DFD is based on the implemented backend modules in `routes/api.php` and the current database schema.

## Level 0 DFD (Context Diagram)

```mermaid
flowchart LR
    client[Client/User]
    coach[Coach]
    admin[Admin]
    payment[Payment Channel - GCash Manual]

    system((Wellness Coaching System))

    client -->|register/login, profile, preferences, logs, progress| system
    system -->|auth status, matches, plans, stats, progress summary| client

    coach -->|login, plans, monitoring requests, notes| system
    system -->|client data, adherence metrics, plan status| coach

    admin -->|login, payment verification, user/coach/content/report actions| system
    system -->|pending payments, dashboard stats, reports| admin

    payment -->|reference details / payment proof source| system
    system -->|verification outcome (recorded internally)| payment
```

## Level 1 DFD (Major Processes)

```mermaid
flowchart TB
    %% External entities
    E1[Client/User]
    E2[Coach]
    E3[Admin]

    %% Processes
    P1((1.0 Authentication & Profile))
    P2((2.0 Subscription & Payment Processing))
    P3((3.0 Matchmaking))
    P4((4.0 Plan Management))
    P5((5.0 Client Activity Logging))
    P6((6.0 Progress Monitoring & Coaching))
    P7((7.0 Admin Management & Reporting))

    %% Data stores
    D1[(D1 Users)]
    D2[(D2 Coaches)]
    D3[(D3 Admins)]
    D4[(D4 Subscriptions)]
    D5[(D5 Payments)]
    D6[(D6 Preferences & Matches)]
    D7[(D7 Plans)]
    D8[(D8 Logs)]
    D9[(D9 Progress Records)]
    D10[(D10 Content Library)]

    %% Auth/Profile
    E1 -->|register/login/update profile| P1
    E2 -->|login/update profile| P1
    E3 -->|login| P1
    P1 --> D1
    P1 --> D2
    P1 --> D3
    P1 -->|session/auth response| E1
    P1 -->|session/auth response| E2
    P1 -->|session/auth response| E3

    %% Subscription/Payments
    E1 -->|view plans, submit payment proof| P2
    E3 -->|approve/reject payment| P2
    P2 --> D4
    P2 --> D5
    P2 -->|subscription status| E1
    P2 -->|pending verifications / history| E3

    %% Matchmaking
    E1 -->|save preferences, select coach| P3
    P3 --> D6
    P3 --> D1
    P3 --> D2
    P3 -->|coach suggestions / active match| E1

    %% Plan management
    E2 -->|create/update/activate/delete plans| P4
    P4 --> D7
    P4 --> D10
    P4 -->|assigned plans| E1
    P4 -->|plan status| E2

    %% Activity logs
    E1 -->|workout/meal/water logs| P5
    P5 --> D8
    P5 -->|log history and stats| E1

    %% Progress monitoring
    E1 -->|measurements and progress photos| P6
    E2 -->|monitoring queries and coach notes| P6
    P6 --> D9
    P6 --> D8
    P6 -->|progress summary| E1
    P6 -->|client adherence and trends| E2

    %% Admin management/reporting
    E3 -->|manage users/coaches/content, request reports| P7
    P7 --> D1
    P7 --> D2
    P7 --> D10
    P7 --> D4
    P7 --> D5
    P7 -->|dashboards and exports| E3
```

## Data Store Mapping to Tables

- **D1 Users**: `users`, `personal_access_tokens`, `sessions`
- **D2 Coaches**: `coaches`
- **D3 Admins**: `admins`
- **D4 Subscriptions**: `subscriptions`
- **D5 Payments**: `payments`
- **D6 Preferences & Matches**: `user_preferences`, `matches`
- **D7 Plans**: `workout_plans`, `meal_plans`
- **D8 Logs**: `workout_logs`, `meal_logs`
- **D9 Progress Records**: `measurements`, `progress_photos`, `coach_notes`
- **D10 Content Library**: `exercises`, `foods`, `generic_content`

## Notes

- Premium feature gate (`subscription` middleware) controls access to matching, plans, logs, and progress flows.
- Messaging (`messages`) is available in schema but omitted from this DFD since current API routes shown focus on core coaching/payment lifecycle.
- If you need, this can be split into separate DFDs per module (Subscription, Coaching, Admin).
