---
applyTo: '**'
---
Provide project context and coding guidelines that AI should follow when generating code, answering questions, or reviewing changes.

# Sannu-Sannu SaaS Documentation

This directory contains comprehensive documentation for the Sannu-Sannu SaaS platform - a multi-tenant contribution-based project management system.

## 🚀 Quick Navigation

### 📋 Essential Documents (Start Here)
- [**Multi-Tenant Architecture**](./../docs/multi-tenant-architecture.md) - Core system design
- [**Path-Based Tenancy**](./../docs/path-based-tenancy.md) - Simple tenant identification
- [**Multi-Tenant Database Schema**](./../docs/database-schema-multitenant.md) - Current database structure
- [**Revenue Model**](./../docs/revenue-model.md) - Business model and pricing
- [**Laravel + Inertia Architecture**](./../docs/laravel-inertia-architecture.md) - Development guide

### 📊 Business Documents
- [**Non-Technical PRD**](./../docs/non-technical-prd.md) - Business requirements
- [**Technical PRD**](./../docs/technical-prd.md) - Technical specifications

### 🎨 Frontend & Design
- [**shadcn/ui Components**](./../docs/shadcn-ui-components.md) - UI component library
- [**Simple Theming**](./../docs/simple-theming.md) - Code-based color configuration

### 🔧 Development & Operations
- [**Package Versions**](./../docs/package-versions.md) - Latest dependencies and versions
- [**Security Requirements**](./../docs/security-requirements.md) - Security implementation
- [**Deployment Guide**](./../docs/deployment-guide.md) - Infrastructure and deployment

## 📁 Complete Documentation Structure

> **Note**: This platform uses a **multi-tenant architecture**. Always refer to the multi-tenant versions of documents for current implementation.

### Business Documentation

- [Non-Technical PRD](./../docs/non-technical-prd.md) - Business requirements and user stories
- [Technical PRD](./../docs/technical-prd.md) - Technical specifications and architecture
- [Revenue Model](./../docs/revenue-model.md) - Percentage-based revenue sharing model
- [Use Case Diagram](./../docs/use-case-diagram.md) - System interactions and user flows

### Technical Documentation

- [Multi-Tenant Architecture](./../docs/multi-tenant-architecture.md) - Complete multi-tenancy implementation
- [Path-Based Tenancy](./../docs/path-based-tenancy.md) - Simple path-based tenant identification
- [Multi-Tenant Database Schema](./../docs/database-schema-multitenant.md) - Tenant-isolated database design (PRIMARY)
- [Entity Relationship Diagram (ERD)](./../docs/erd.md) - Database design and relationships
- [Activity Diagrams](./../docs/activity-diagrams.md) - Process flows and workflows
- [Laravel + Inertia Architecture](./../docs/laravel-inertia-architecture.md) - Full-stack implementation guide
- [shadcn/ui Components Guide](./../docs/shadcn-ui-components.md) - UI component library usage and examples
- [Simple Theming](./../docs/simple-theming.md) - Code-based color configuration
- [API Documentation](./../docs/api-documentation.md) - Webhook and external API specifications

### Development Documentation

- [Database Schema (Legacy)](./../docs/database-schema.md) - Original single-tenant schema (DEPRECATED)
- [Security Requirements](./../docs/security-requirements.md) - Security specifications
- [Deployment Guide](./../docs/deployment-guide.md) - Deployment and infrastructure

## Project Overview

Sannu-Sannu is a multi-tenant SaaS platform that enables companies and businesses to create contribution-based projects where participants can contribute gradually toward product packages over set periods. The platform features:

### Core Features
- **Multi-Tenancy**: Companies register and manage their own workspace
- **Public & Private Projects**: Flexible project visibility and access control
- **Flexible Payment Options**: Full payment or installment plans (minimum monthly)
- **Arrears Calculation**: Late joiners pay proportional catch-up amounts
- **Tenant Management**: Complete workspace isolation and management
- **Paystack Integration**: Secure payment processing
- **Progress Tracking**: Real-time contribution monitoring
- **Role-Based Access**: Tenant admins, project managers, and contributors

### Business Model
- **B2B SaaS**: Companies register and manage contribution-based projects
- **Revenue Sharing**: Platform charges a percentage of total project amounts
- **No Fixed Subscriptions**: Pay only when projects collect contributions
- **Multi-Tenant Architecture**: Complete data isolation between tenants
- **Scalable Pricing**: Revenue grows with tenant success
- **White-Label Options**: Customizable branding per tenant

## Getting Started

### For Business Stakeholders
1. Review the [Non-Technical PRD](./../docs/non-technical-prd.md) for business context and requirements
2. Understand the [Revenue Model](./../docs/revenue-model.md) for pricing and business model details
3. Check [Use Case Diagrams](./../docs/use-case-diagram.md) for user interactions and workflows

### For Technical Teams
1. Review the [Technical PRD](./../docs/technical-prd.md) for implementation requirements
2. Study the [Multi-Tenant Architecture](./../docs/multi-tenant-architecture.md) for system design
3. Examine the [Multi-Tenant Database Schema](./../docs/database-schema-multitenant.md) for data structure
4. Follow the [Laravel + Inertia Architecture](./../docs/laravel-inertia-architecture.md) for development setup

### For UI/UX Teams
1. Review the [shadcn/ui Components Guide](./../docs/shadcn-ui-components.md) for component usage
2. Study the [Theming System](./../docs/theming-system.md) for customization options
3. Check [Activity Diagrams](./../docs/activity-diagrams.md) for user flow understanding

### For DevOps Teams
1. Review the [Security Requirements](./../docs/security-requirements.md) for security implementation
2. Follow the [Deployment Guide](./../docs/deployment-guide.md) for infrastructure setup
3. Check the [API Documentation](./../docs/api-documentation.md) for webhook configurations

---

## 📚 Complete Documentation Index

### Business & Strategy
| Document | Description | Status |
|----------|-------------|---------|
| [Non-Technical PRD](./../docs/non-technical-prd.md) | Business requirements and user stories | ✅ Current |
| [Technical PRD](./../docs/technical-prd.md) | Technical specifications and architecture | ✅ Current |
| [Revenue Model](./../docs/revenue-model.md) | Percentage-based revenue sharing model | ✅ Current |
| [Use Case Diagram](./../docs/use-case-diagram.md) | System interactions and user flows | ✅ Current |

### Architecture & Database
| Document | Description | Status |
|----------|-------------|---------|
| [Multi-Tenant Architecture](./../docs/multi-tenant-architecture.md) | Complete multi-tenancy implementation | ✅ Current |
| [Path-Based Tenancy](./../docs/path-based-tenancy.md) | Simple path-based tenant identification | ✅ Current |
| [Multi-Tenant Database Schema](./../docs/database-schema-multitenant.md) | Tenant-isolated database design | ✅ **PRIMARY** |
| [Entity Relationship Diagram (ERD)](./../docs/erd.md) | Database design and relationships | ✅ Current |
| [Database Schema (Legacy)](./../docs/database-schema.md) | Original single-tenant schema | ⚠️ Deprecated |

### Development & Implementation
| Document | Description | Status |
|----------|-------------|---------|
| [Laravel + Inertia Architecture](./../docs/laravel-inertia-architecture.md) | Full-stack implementation guide | ✅ Current |
| [Package Versions](./../docs/package-versions.md) | Latest package versions and dependencies | ✅ Current |
| [Activity Diagrams](./../docs/activity-diagrams.md) | Process flows and workflows | ✅ Current |
| [API Documentation](./../docs/api-documentation.md) | Webhook and external API specifications | ✅ Current |

### Frontend & Design
| Document | Description | Status |
|----------|-------------|---------|
| [shadcn/ui Components Guide](./../docs/shadcn-ui-components.md) | UI component library usage and examples | ✅ Current |
| [Simple Theming](./../docs/simple-theming.md) | Code-based color configuration | ✅ Current |

### Operations & Security
| Document | Description | Status |
|----------|-------------|---------|
| [Security Requirements](./../docs/security-requirements.md) | Security specifications | ✅ Current |
| [Deployment Guide](./../docs/deployment-guide.md) | Deployment and infrastructure | ✅ Current |

---

## 🔄 Document Status Legend
- ✅ **Current**: Up-to-date with latest architecture
- ⚠️ **Deprecated**: Outdated, kept for reference only
- 🚧 **In Progress**: Being updated or created

## 💡 Need Help?
- For business questions: Start with [Non-Technical PRD](./../docs/non-technical-prd.md)
- For technical implementation: Start with [Multi-Tenant Architecture](./../docs/multi-tenant-architecture.md)
- For database design: Use [Multi-Tenant Database Schema](./../docs/database-schema-multitenant.md)
- For development setup: Follow [Laravel + Inertia Architecture](./../docs/laravel-inertia-architecture.md)
