# Activity Diagrams
## Sannu-Sannu SaaS Platform

### Overview

This document contains activity diagrams for key business processes in the Sannu-Sannu platform, showing the flow of activities and decision points for critical user journeys.

---

## 1. User Registration Process

```mermaid
flowchart TD
    A[User visits registration page] --> B[Fill registration form]
    B --> C{Valid input?}
    C -->|No| D[Show validation errors]
    D --> B
    C -->|Yes| E[Check email uniqueness]
    E --> F{Email exists?}
    F -->|Yes| G[Show email exists error]
    G --> B
    F -->|No| H[Hash password]
    H --> I[Create user record]
    I --> J[Generate verification token]
    J --> K[Send verification email]
    K --> L{Email sent?}
    L -->|No| M[Show email error]
    M --> N[User can resend]
    N --> K
    L -->|Yes| O[Show success message]
    O --> P[User checks email]
    P --> Q[Click verification link]
    Q --> R{Valid token?}
    R -->|No| S[Show invalid token error]
    R -->|Yes| T[Mark email as verified]
    T --> U[Auto-login user]
    U --> V[Redirect to dashboard]
```

---

## 2. Project Joining Process

```mermaid
flowchart TD
    A[User browses projects] --> B[Select project]
    B --> C[View project details]
    C --> D{User logged in?}
    D -->|No| E[Redirect to login]
    E --> F[Login successful]
    F --> C
    D -->|Yes| G{Already joined?}
    G -->|Yes| H[Show already joined message]
    G -->|No| I[Show join project form]
    I --> J[Select contribution duration]
    J --> K[Choose payment type]
    K --> L{Payment type?}
    L -->|Monthly| M[Calculate monthly amount]
    L -->|One-time| N[Calculate total amount]
    M --> O{Joining mid-project?}
    N --> O
    O -->|Yes| P[Calculate arrears]
    O -->|No| Q[No arrears needed]
    P --> R[Show total commitment]
    Q --> R
    R --> S[User confirms participation]
    S --> T[Create contribution record]
    T --> U{Payment required now?}
    U -->|Yes| V[Initialize payment]
    U -->|No| W[Schedule first payment]
    V --> X[Redirect to Paystack]
    W --> Y[Show success message]
    X --> Z[Payment processing]
    Z --> AA{Payment successful?}
    AA -->|Yes| Y
    AA -->|No| BB[Show payment error]
    BB --> CC[Retry payment option]
    CC --> V
    Y --> DD[Redirect to dashboard]
```

---

## 3. Payment Processing Workflow

```mermaid
flowchart TD
    A[Payment initiated] --> B[Create transaction record]
    B --> C[Generate Paystack reference]
    C --> D[Initialize Paystack payment]
    D --> E{Paystack response?}
    E -->|Error| F[Log error]
    F --> G[Show error to user]
    E -->|Success| H[Redirect to Paystack]
    H --> I[User enters payment details]
    I --> J[Paystack processes payment]
    J --> K[Paystack sends webhook]
    K --> L[Verify webhook signature]
    L --> M{Signature valid?}
    M -->|No| N[Log security warning]
    N --> O[Ignore webhook]
    M -->|Yes| P[Parse webhook data]
    P --> Q{Payment successful?}
    Q -->|No| R[Update transaction as failed]
    R --> S[Send failure email]
    S --> T[Update contribution status]
    Q -->|Yes| U[Update transaction as successful]
    U --> V[Update contribution balance]
    V --> W[Update payment schedule]
    W --> X[Send success email]
    X --> Y[Check if contribution complete]
    Y --> Z{Fully paid?}
    Z -->|Yes| AA[Mark contribution complete]
    Z -->|No| BB[Schedule next payment]
    AA --> CC[Send completion email]
    BB --> DD[End process]
    CC --> DD
    T --> DD
```

---

## 4. Admin Project Creation Process

```mermaid
flowchart TD
    A[Admin accesses project creation] --> B[Fill project basic info]
    B --> C[Set project dates]
    C --> D{Valid date range?}
    D -->|No| E[Show date validation error]
    E --> C
    D -->|Yes| F[Add products to project]
    F --> G[Set product details]
    G --> H[Upload product images]
    H --> I{More products?}
    I -->|Yes| F
    I -->|No| J[Calculate project totals]
    J --> K[Review project summary]
    K --> L{Admin confirms?}
    L -->|No| M[Return to editing]
    M --> B
    L -->|Yes| N[Save project as draft]
    N --> O[Show success message]
    O --> P{Activate now?}
    P -->|No| Q[End process]
    P -->|Yes| R[Validate project completeness]
    R --> S{Project complete?}
    S -->|No| T[Show validation errors]
    T --> M
    S -->|Yes| U[Activate project]
    U --> V[Make project visible]
    V --> W[Send activation notifications]
    W --> Q
```

---

## 5. Monthly Payment Processing (Automated)

```mermaid
flowchart TD
    A[Scheduled job runs] --> B[Query due payments]
    B --> C{Payments found?}
    C -->|No| D[End process]
    C -->|Yes| E[For each due payment]
    E --> F[Check contribution status]
    F --> G{Contribution active?}
    G -->|No| H[Skip payment]
    G -->|Yes| I[Create transaction record]
    I --> J[Initialize Paystack payment]
    J --> K{Paystack success?}
    K -->|No| L[Mark payment failed]
    L --> M[Send failure notification]
    K -->|Yes| N[Process payment response]
    N --> O{Payment successful?}
    O -->|No| P[Update failed status]
    P --> Q[Increment retry count]
    Q --> R{Max retries reached?}
    R -->|No| S[Schedule retry]
    R -->|Yes| T[Mark as failed]
    T --> U[Send final failure notice]
    O -->|Yes| V[Update contribution balance]
    V --> W[Mark payment as paid]
    W --> X[Send success notification]
    X --> Y[Schedule next payment]
    Y --> Z{More payments?}
    S --> Z
    U --> Z
    M --> Z
    H --> Z
    Z -->|Yes| E
    Z -->|No| AA[Generate summary report]
    AA --> BB[Send admin notification]
    BB --> D
```

---

## 6. User Dashboard Data Loading

```mermaid
flowchart TD
    A[User accesses dashboard] --> B[Authenticate user]
    B --> C{Authentication valid?}
    C -->|No| D[Redirect to login]
    C -->|Yes| E[Load user contributions]
    E --> F[Calculate progress for each project]
    F --> G[Load recent transactions]
    G --> H[Load upcoming payments]
    H --> I[Load notifications]
    I --> J[Check for overdue payments]
    J --> K{Overdue payments?}
    K -->|Yes| L[Add overdue alerts]
    K -->|No| M[Compile dashboard data]
    L --> M
    M --> N[Render dashboard view]
    N --> O[Load project recommendations]
    O --> P[Display complete dashboard]
```

---

## 7. Admin Export Process

```mermaid
flowchart TD
    A[Admin selects export option] --> B[Choose project]
    B --> C[Select export format]
    C --> D[Choose date range]
    D --> E[Confirm export parameters]
    E --> F[Query contributor data]
    F --> G[Apply filters and sorting]
    G --> H{Export format?}
    H -->|CSV| I[Generate CSV file]
    H -->|PDF| J[Generate PDF report]
    H -->|Excel| K[Generate Excel file]
    I --> L[Save file to temp location]
    J --> L
    K --> L
    L --> M[Generate download link]
    M --> N[Send download email to admin]
    N --> O[Show download page]
    O --> P[Admin downloads file]
    P --> Q[Schedule file cleanup]
    Q --> R[End process]
```

---

## 8. Email Notification Process

```mermaid
flowchart TD
    A[Notification trigger event] --> B[Determine notification type]
    B --> C[Load email template]
    C --> D[Populate template data]
    D --> E[Create notification record]
    E --> F[Queue email for sending]
    F --> G[Email processor picks up job]
    G --> H[Connect to SMTP server]
    H --> I{Connection successful?}
    I -->|No| J[Log connection error]
    J --> K[Increment retry count]
    K --> L{Max retries reached?}
    L -->|No| M[Requeue email]
    L -->|Yes| N[Mark as failed]
    N --> O[Log final failure]
    I -->|Yes| P[Send email]
    P --> Q{Email sent successfully?}
    Q -->|No| R[Log send error]
    R --> K
    Q -->|Yes| S[Mark as sent]
    S --> T[Update notification record]
    T --> U[Log successful delivery]
    U --> V[End process]
    M --> G
    O --> V
```

---

## 9. Project Lifecycle Management

```mermaid
flowchart TD
    A[Project created in draft] --> B{Admin activates?}
    B -->|No| C[Remains in draft]
    C --> D[Admin can edit]
    D --> B
    B -->|Yes| E[Validate project data]
    E --> F{Validation passed?}
    F -->|No| G[Show validation errors]
    G --> D
    F -->|Yes| H[Set status to active]
    H --> I[Make visible to users]
    I --> J[Users can join project]
    J --> K[Monitor project progress]
    K --> L{End date reached?}
    L -->|No| M{Admin pauses?}
    M -->|No| K
    M -->|Yes| N[Set status to paused]
    N --> O[Stop accepting new users]
    O --> P{Admin resumes?}
    P -->|Yes| H
    P -->|No| Q{Admin cancels?}
    Q -->|Yes| R[Set status to cancelled]
    Q -->|No| N
    L -->|Yes| S[Set status to completed]
    S --> T[Process final payments]
    T --> U[Generate completion reports]
    U --> V[Send completion notifications]
    V --> W[Archive project data]
    R --> X[Process cancellation]
    X --> Y[Handle refunds if needed]
    Y --> Z[Send cancellation notices]
    Z --> W
    W --> AA[End lifecycle]
```

---

## 10. Error Handling and Recovery

```mermaid
flowchart TD
    A[System error occurs] --> B[Log error details]
    B --> C[Determine error type]
    C --> D{Critical error?}
    D -->|Yes| E[Send admin alert]
    E --> F[Display maintenance page]
    F --> G[Admin investigates]
    G --> H[Apply fix]
    H --> I[Test system]
    I --> J{System stable?}
    J -->|No| G
    J -->|Yes| K[Remove maintenance page]
    D -->|No| L{User-facing error?}
    L -->|Yes| M[Show user-friendly message]
    M --> N[Provide recovery options]
    N --> O[Log user action]
    L -->|No| P[Background error handling]
    P --> Q[Attempt automatic recovery]
    Q --> R{Recovery successful?}
    R -->|Yes| S[Log recovery]
    R -->|No| T[Escalate to admin]
    T --> E
    S --> U[Continue normal operation]
    O --> U
    K --> U
    U --> V[End process]
```

### Activity Diagram Conventions

#### Symbols Used
- **Rounded rectangles**: Activities/Actions
- **Diamonds**: Decision points
- **Circles**: Start/End points
- **Arrows**: Flow direction
- **Parallel bars**: Concurrent activities

#### Decision Logic
- Each decision point shows the condition being evaluated
- Alternative paths are clearly labeled (Yes/No, specific conditions)
- Error paths and recovery mechanisms are included
- Loops and iterations are properly represented

#### Process Integration
- Activities show integration points with external systems (Paystack, Email)
- Database operations are implied within activities
- User interactions are clearly distinguished from system processes
- Admin actions are separated from user actions

These activity diagrams provide a comprehensive view of the system's operational flows and can be used for:
- Development planning and implementation
- Quality assurance testing scenarios
- User training and documentation
- System maintenance and troubleshooting
- Business process optimization