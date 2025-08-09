# External API & Webhook Documentation

## Sannu-Sannu SaaS Platform

### Overview

This document covers external API integrations and webhook handling for the Sannu-Sannu platform. The main application uses Laravel + Inertia.js architecture (see [Laravel + Inertia Architecture](./laravel-inertia-architecture.md) for internal implementation details).

### External Integrations

#### Paystack API Integration

The platform integrates with Paystack for payment processing using their REST API.

**Base URL**: `https://api.paystack.co`
**Authentication**: Bearer token using secret key

#### Response Format

```json
{
  "success": true,
  "data": {},
  "message": "Operation completed successfully",
  "errors": [],
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0"
  }
}
```

---

## Paystack API Endpoints

### Initialize Payment

**Endpoint**: `POST https://api.paystack.co/transaction/initialize`

**Headers**:

```
Authorization: Bearer sk_test_xxxxx
Content-Type: application/json
```

**Request Body:**

```json
{
  "email": "user@example.com",
  "amount": 5000,
  "reference": "T_abc123def456",
  "callback_url": "https://sannu-sannu.com/payments/callback",
  "metadata": {
    "user_id": 1,
    "project_id": 1,
    "contribution_id": 1,
    "payment_type": "monthly"
  }
}
```

**Response (200 OK):**

```json
{
  "status": true,
  "message": "Authorization URL created",
  "data": {
    "authorization_url": "https://checkout.paystack.com/abc123def456",
    "access_code": "abc123def456",
    "reference": "T_abc123def456"
  }
}
```

### POST /auth/login

Authenticate user and create session.

**Request Body:**

```json
{
  "email": "john@example.com",
  "password": "securePassword123",
  "remember": false
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "email_verified_at": "2025-01-15T10:35:00Z"
    },
    "session_expires": "2025-01-15T22:30:00Z"
  },
  "message": "Login successful"
}
```

### POST /auth/logout

End user session.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### GET /auth/profile

Get current user profile information.

**Headers:** `Authorization: Bearer {session_token}`

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "email_verified_at": "2025-01-15T10:35:00Z",
      "created_at": "2025-01-15T10:30:00Z",
      "updated_at": "2025-01-15T10:35:00Z"
    }
  }
}
```

---

## Project Endpoints

### GET /projects

List all active projects available for participation.

**Query Parameters:**

- `status` (optional): Filter by project status
- `page` (optional): Page number for pagination (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `search` (optional): Search in project name and description

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "projects": [
      {
        "id": 1,
        "name": "Premium Product Package Q1",
        "description": "Quarterly package including premium products",
        "monthly_amount": 50.0,
        "start_date": "2025-01-01",
        "end_date": "2025-03-31",
        "total_project_value": 1500.0,
        "status": "active",
        "products": [
          {
            "id": 1,
            "name": "Premium Widget",
            "description": "High-quality widget",
            "price": 75.0,
            "image_url": "https://example.com/widget.jpg"
          }
        ],
        "statistics": {
          "total_contributors": 25,
          "total_collected": 750.0,
          "completion_percentage": 50.0
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "total_items": 25,
      "items_per_page": 10
    }
  }
}
```

### GET /projects/{id}

Get detailed information about a specific project.

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "project": {
      "id": 1,
      "name": "Premium Product Package Q1",
      "description": "Quarterly package including premium products",
      "monthly_amount": 50.0,
      "start_date": "2025-01-01",
      "end_date": "2025-03-31",
      "total_project_value": 1500.0,
      "status": "active",
      "created_at": "2024-12-15T10:00:00Z",
      "products": [
        {
          "id": 1,
          "name": "Premium Widget",
          "description": "High-quality widget with advanced features",
          "price": 75.0,
          "image_url": "https://example.com/widget.jpg",
          "sort_order": 1
        }
      ],
      "statistics": {
        "total_contributors": 25,
        "total_committed": 1250.0,
        "total_collected": 750.0,
        "completion_percentage": 50.0,
        "months_remaining": 2
      }
    }
  }
}
```

### POST /projects (Admin Only)

Create a new project.

**Headers:** `Authorization: Bearer {admin_session_token}`

**Request Body:**

```json
{
  "name": "Summer Product Package",
  "description": "Special summer collection of products",
  "monthly_amount": 75.0,
  "start_date": "2025-06-01",
  "end_date": "2025-08-31",
  "products": [
    {
      "name": "Summer Widget",
      "description": "Seasonal widget variant",
      "price": 100.0,
      "image_url": "https://example.com/summer-widget.jpg"
    }
  ]
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "project": {
      "id": 2,
      "name": "Summer Product Package",
      "status": "draft",
      "total_project_value": 300.0,
      "created_at": "2025-01-15T10:30:00Z"
    }
  },
  "message": "Project created successfully"
}
```

---

## Contribution Endpoints

### GET /contributions

Get current user's contributions.

**Headers:** `Authorization: Bearer {session_token}`

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "contributions": [
      {
        "id": 1,
        "project": {
          "id": 1,
          "name": "Premium Product Package Q1",
          "status": "active"
        },
        "monthly_amount": 50.0,
        "duration_months": 3,
        "total_committed": 150.0,
        "total_paid": 100.0,
        "remaining_balance": 50.0,
        "payment_type": "monthly",
        "status": "active",
        "joined_date": "2025-01-01",
        "next_payment_due": "2025-02-01",
        "progress_percentage": 66.67
      }
    ],
    "summary": {
      "total_projects": 1,
      "total_committed": 150.0,
      "total_paid": 100.0,
      "total_outstanding": 50.0
    }
  }
}
```

### POST /contributions

Join a project and create a contribution.

**Headers:** `Authorization: Bearer {session_token}`

**Request Body:**

```json
{
  "project_id": 1,
  "duration_months": 3,
  "payment_type": "monthly"
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "contribution": {
      "id": 2,
      "project_id": 1,
      "monthly_amount": 50.0,
      "duration_months": 3,
      "total_committed": 150.0,
      "arrears_amount": 25.0,
      "payment_type": "monthly",
      "status": "active",
      "joined_date": "2025-01-15"
    },
    "immediate_payment_required": true,
    "payment_amount": 75.0
  },
  "message": "Successfully joined project. Payment required to complete enrollment."
}
```

### GET /contributions/{id}

Get detailed information about a specific contribution.

**Headers:** `Authorization: Bearer {session_token}`

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "contribution": {
      "id": 1,
      "project": {
        "id": 1,
        "name": "Premium Product Package Q1",
        "description": "Quarterly package including premium products"
      },
      "monthly_amount": 50.0,
      "duration_months": 3,
      "total_committed": 150.0,
      "total_paid": 100.0,
      "arrears_amount": 0.0,
      "payment_type": "monthly",
      "status": "active",
      "joined_date": "2025-01-01",
      "next_payment_due": "2025-02-01",
      "payment_history": [
        {
          "id": 1,
          "amount": 50.0,
          "type": "monthly",
          "status": "success",
          "processed_at": "2025-01-01T12:00:00Z"
        },
        {
          "id": 2,
          "amount": 50.0,
          "type": "monthly",
          "status": "success",
          "processed_at": "2025-01-15T12:00:00Z"
        }
      ]
    }
  }
}
```

---

## Payment Endpoints

### POST /payments/initialize

Initialize a payment with Paystack.

**Headers:** `Authorization: Bearer {session_token}`

**Request Body:**

```json
{
  "contribution_id": 1,
  "amount": 50.0,
  "type": "monthly"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": 3,
      "paystack_reference": "T_abc123def456",
      "amount": 50.0,
      "status": "pending"
    },
    "paystack_data": {
      "authorization_url": "https://checkout.paystack.com/abc123def456",
      "access_code": "abc123def456",
      "reference": "T_abc123def456"
    }
  },
  "message": "Payment initialized successfully"
}
```

### POST /payments/verify

Verify payment status after Paystack callback.

**Request Body:**

```json
{
  "reference": "T_abc123def456"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": 3,
      "paystack_reference": "T_abc123def456",
      "amount": 50.0,
      "status": "success",
      "processed_at": "2025-01-15T12:30:00Z"
    },
    "contribution_updated": true,
    "new_balance": {
      "total_paid": 150.0,
      "remaining_balance": 0.0,
      "status": "completed"
    }
  },
  "message": "Payment verified and processed successfully"
}
```

### GET /payments/history

Get user's payment history.

**Headers:** `Authorization: Bearer {session_token}`

**Query Parameters:**

- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20)
- `status` (optional): Filter by payment status
- `from_date` (optional): Start date filter (YYYY-MM-DD)
- `to_date` (optional): End date filter (YYYY-MM-DD)

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 3,
        "contribution": {
          "id": 1,
          "project_name": "Premium Product Package Q1"
        },
        "amount": 50.0,
        "type": "monthly",
        "status": "success",
        "paystack_reference": "T_abc123def456",
        "processed_at": "2025-01-15T12:30:00Z",
        "created_at": "2025-01-15T12:25:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 2,
      "total_items": 15,
      "items_per_page": 20
    },
    "summary": {
      "total_amount": 750.0,
      "successful_payments": 14,
      "failed_payments": 1
    }
  }
}
```

---

## Admin Endpoints

### GET /admin/users

List all users (Admin only).

**Headers:** `Authorization: Bearer {admin_session_token}`

**Query Parameters:**

- `page`, `limit`, `search` (same as projects)
- `role` (optional): Filter by user role

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user",
        "email_verified_at": "2025-01-15T10:35:00Z",
        "created_at": "2025-01-15T10:30:00Z",
        "statistics": {
          "total_contributions": 2,
          "total_committed": 300.0,
          "total_paid": 200.0
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 47,
      "items_per_page": 10
    }
  }
}
```

### GET /admin/projects/{id}/contributors

Get list of contributors for a specific project.

**Headers:** `Authorization: Bearer {admin_session_token}`

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "project": {
      "id": 1,
      "name": "Premium Product Package Q1"
    },
    "contributors": [
      {
        "user_id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "contribution": {
          "id": 1,
          "monthly_amount": 50.0,
          "total_committed": 150.0,
          "total_paid": 100.0,
          "status": "active",
          "joined_date": "2025-01-01",
          "progress_percentage": 66.67
        }
      }
    ],
    "summary": {
      "total_contributors": 25,
      "total_committed": 3750.0,
      "total_collected": 2500.0,
      "average_contribution": 150.0
    }
  }
}
```

### GET /admin/export/{project_id}

Export contributor data for a project.

**Headers:** `Authorization: Bearer {admin_session_token}`

**Query Parameters:**

- `format`: Export format (csv, excel, pdf)
- `include_payments`: Include payment history (true/false)

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "export": {
      "id": "exp_abc123",
      "format": "csv",
      "status": "processing",
      "download_url": null,
      "expires_at": "2025-01-16T10:30:00Z"
    }
  },
  "message": "Export is being processed. You will receive an email when ready."
}
```

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Authentication Error (401 Unauthorized)

```json
{
  "success": false,
  "message": "Authentication required",
  "errors": ["Please log in to access this resource"],
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Authorization Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Insufficient permissions",
  "errors": ["Admin access required for this operation"],
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Not Found Error (404 Not Found)

```json
{
  "success": false,
  "message": "Resource not found",
  "errors": ["The requested project does not exist"],
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Internal server error",
  "errors": ["An unexpected error occurred. Please try again later."],
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z",
    "version": "1.0.0",
    "error_id": "err_abc123def456"
  }
}
```

---

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Authentication endpoints**: 5 requests per minute per IP
- **General endpoints**: 100 requests per minute per authenticated user
- **Admin endpoints**: 200 requests per minute per admin user
- **Payment endpoints**: 10 requests per minute per user

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642248600
```

---

## Webhooks

### Paystack Webhook

Endpoint: `POST /webhooks/paystack`

The system receives payment notifications from Paystack and processes them automatically. Webhook signature verification is implemented for security.

**Webhook Payload Example:**

```json
{
  "event": "charge.success",
  "data": {
    "id": 123456789,
    "reference": "T_abc123def456",
    "amount": 5000,
    "status": "success",
    "customer": {
      "email": "john@example.com"
    },
    "metadata": {
      "user_id": 1,
      "contribution_id": 1
    }
  }
}
```

---

## SDK Examples

### JavaScript/Node.js

```javascript
const SannuSannuAPI = require("sannu-sannu-sdk");

const client = new SannuSannuAPI({
  baseURL: "https://api.sannu-sannu.com/v1",
  sessionToken: "your-session-token",
});

// Get user's contributions
const contributions = await client.contributions.list();

// Join a project
const newContribution = await client.contributions.create({
  project_id: 1,
  duration_months: 3,
  payment_type: "monthly",
});
```

### PHP

```php
<?php
use SannuSannu\API\Client;

$client = new Client([
    'base_url' => 'https://api.sannu-sannu.com/v1',
    'session_token' => 'your-session-token'
]);

// Get projects
$projects = $client->projects()->list();

// Initialize payment
$payment = $client->payments()->initialize([
    'contribution_id' => 1,
    'amount' => 50.00,
    'type' => 'monthly'
]);
?>
```

This API documentation provides comprehensive coverage of all endpoints, request/response formats, error handling, and integration examples for the Sannu-Sannu platform.
