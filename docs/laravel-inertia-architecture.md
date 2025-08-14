# Laravel + Inertia.js + React Architecture
## Sannu-Sannu SaaS Platform

### Architecture Overview

The Sannu-Sannu platform uses a modern full-stack architecture combining Laravel's robust backend capabilities with React's dynamic frontend, seamlessly connected through Inertia.js for a single-page application experience without the complexity of a separate API.

### Technology Stack Details

#### Backend (Laravel 12.x)
- **Framework**: Laravel 12.x with PHP 8.4+
- **Authentication**: Laravel Sanctum 4.x with session-based authentication
- **Database**: MySQL 8.4+ with Eloquent ORM 12.x
- **Queue System**: Redis 7.x-backed queues for background processing
- **Email**: Laravel Mail 12.x with queue support and Markdown templates
- **File Storage**: Laravel Storage 12.x with local/S3/CloudFlare R2 drivers
- **Caching**: Redis 7.x for application cache, sessions, and rate limiting
- **Multi-Tenancy**: Laravel Tenancy package for tenant isolation

#### Frontend (React 19.x + TypeScript 5.x)
- **Framework**: React 19.x with TypeScript 5.x for enhanced type safety
- **Bridge**: Inertia.js 2.x for seamless Laravel-React integration
- **Styling**: Tailwind CSS 4.x with shadcn/ui component library
- **UI Components**: shadcn/ui latest with Radix UI primitives
- **State Management**: React Context API with React 19 optimizations
- **Forms**: React Hook Form 7.x with Inertia 2.x form helpers
- **Build Tool**: Vite 6.x for ultra-fast development and optimized builds
- **Package Manager**: pnpm 9.x for efficient dependency management

#### Development Environment
- **Package Manager**: pnpm 9.x for efficient dependency management
- **Code Quality**: ESLint 9.x, Prettier 3.x, PHP CS Fixer 3.x, PHPStan 1.x
- **Testing**: PHPUnit 11.x (backend), Vitest + React Testing Library (frontend)
- **Version Control**: Git 2.40+ with conventional commits
- **Performance**: PHP OPcache, Redis for caching, Vite for HMR

---

## Project Structure

```
sannu-sannu/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ContributionController.php
│   │   │   └── PaymentController.php
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests.php
│   │   │   └── AdminMiddleware.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Project.php
│   │   ├── Contribution.php
│   │   ├── Transaction.php
│   │   └── Product.php
│   ├── Services/
│   │   ├── PaystackService.php
│   │   ├── ContributionService.php
│   │   └── ProjectService.php
│   ├── Jobs/
│   │   ├── ProcessPaymentJob.php
│   │   ├── SendPaymentReminderJob.php
│   │   └── GenerateReportJob.php
│   ├── Mail/
│   │   ├── PaymentConfirmation.php
│   │   ├── PaymentReminder.php
│   │   └── ProjectJoined.php
│   └── Enums/
│       ├── UserRole.php
│       ├── ProjectStatus.php
│       └── PaymentType.php
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── ui/              # shadcn/ui components
│   │   │   │   ├── button.tsx
│   │   │   │   ├── card.tsx
│   │   │   │   ├── input.tsx
│   │   │   │   ├── dialog.tsx
│   │   │   │   └── ...
│   │   │   ├── Forms/
│   │   │   ├── Layout/
│   │   │   └── Project/
│   │   ├── Pages/
│   │   │   ├── Auth/
│   │   │   ├── Dashboard/
│   │   │   ├── Projects/
│   │   │   ├── Contributions/
│   │   │   └── Admin/
│   │   ├── Hooks/
│   │   ├── Utils/
│   │   ├── Types/
│   │   ├── lib/
│   │   │   └── utils.ts         # shadcn/ui utilities
│   │   └── app.tsx
│   ├── views/
│   │   └── app.blade.php
│   └── css/
│       └── app.css
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── admin.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── tests/
│   ├── Feature/
│   └── Unit/
├── package.json
├── vite.config.js
├── tailwind.config.js
└── tsconfig.json
```

---

## Laravel Backend Implementation

### Controllers

#### Dashboard Controller
```php
<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $contributions = $user->contributions()
            ->with(['project', 'transactions' => fn($q) => $q->latest()->limit(5)])
            ->get()
            ->map(function ($contribution) {
                return [
                    'id' => $contribution->id,
                    'project' => [
                        'id' => $contribution->project->id,
                        'name' => $contribution->project->name,
                        'status' => $contribution->project->status,
                    ],
                    'monthly_amount' => $contribution->monthly_amount,
                    'total_committed' => $contribution->total_committed,
                    'total_paid' => $contribution->total_paid,
                    'remaining_balance' => $contribution->remaining_balance,
                    'progress_percentage' => $contribution->progress_percentage,
                    'status' => $contribution->status,
                    'next_payment_due' => $contribution->next_payment_due,
                ];
            });

        $availableProjects = Project::active()
            ->whereDoesntHave('contributions', fn($q) => $q->where('user_id', $user->id))
            ->with('products')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'contributions' => $contributions,
            'availableProjects' => $availableProjects,
            'summary' => [
                'total_projects' => $contributions->count(),
                'total_committed' => $contributions->sum('total_committed'),
                'total_paid' => $contributions->sum('total_paid'),
                'total_outstanding' => $contributions->sum('remaining_balance'),
            ],
        ]);
    }
}
```

#### Project Controller
```php
<?php
// app/Http/Controllers/ProjectController.php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::active()
            ->with(['products', 'contributions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->paginate(12)
            ->through(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'monthly_amount' => $project->monthly_amount,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'total_project_value' => $project->total_project_value,
                    'products' => $project->products,
                    'statistics' => [
                        'total_contributors' => $project->contributions->count(),
                        'total_collected' => $project->contributions->sum('total_paid'),
                        'completion_percentage' => ($project->contributions->sum('total_paid') / $project->total_project_value) * 100,
                    ],
                ];
            });

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Project $project): Response
    {
        $project->load(['products', 'contributions.user']);
        
        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'monthly_amount' => $project->monthly_amount,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'total_project_value' => $project->total_project_value,
                'status' => $project->status,
                'products' => $project->products,
                'statistics' => [
                    'total_contributors' => $project->contributions->count(),
                    'total_committed' => $project->contributions->sum('total_committed'),
                    'total_collected' => $project->contributions->sum('total_paid'),
                    'completion_percentage' => ($project->contributions->sum('total_paid') / $project->total_project_value) * 100,
                    'months_remaining' => now()->diffInMonths($project->end_date),
                ],
            ],
            'userContribution' => auth()->user()?->contributions()
                ->where('project_id', $project->id)
                ->first(),
        ]);
    }
}
```

### Services

#### Paystack Service
```php
<?php
// app/Services/PaystackService.php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->baseUrl = 'https://api.paystack.co';
    }

    public function initializePayment(array $data): array
    {
        $response = $this->makeRequest('POST', '/transaction/initialize', [
            'email' => $data['email'],
            'amount' => $data['amount'] * 100, // Convert to kobo
            'reference' => $data['reference'],
            'metadata' => $data['metadata'],
            'callback_url' => route('payments.callback'),
        ]);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        throw new \Exception('Failed to initialize payment: ' . $response->body());
    }

    public function verifyPayment(string $reference): array
    {
        $response = $this->makeRequest('GET', "/transaction/verify/{$reference}");

        if ($response->successful()) {
            return $response->json()['data'];
        }

        throw new \Exception('Failed to verify payment: ' . $response->body());
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ]);

        return match ($method) {
            'GET' => $request->get($this->baseUrl . $endpoint),
            'POST' => $request->post($this->baseUrl . $endpoint, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }
}
```

---

## React Frontend Implementation

### TypeScript Types
```typescript
// resources/js/Types/index.ts

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'user';
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Project {
  id: number;
  name: string;
  description: string;
  monthly_amount: number;
  start_date: string;
  end_date: string;
  total_project_value: number;
  status: 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';
  products: Product[];
  statistics?: {
    total_contributors: number;
    total_collected: number;
    completion_percentage: number;
    months_remaining?: number;
  };
}

export interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  image_url?: string;
  sort_order: number;
}

export interface Contribution {
  id: number;
  project: Pick<Project, 'id' | 'name' | 'status'>;
  monthly_amount: number;
  duration_months: number;
  total_committed: number;
  total_paid: number;
  remaining_balance: number;
  payment_type: 'monthly' | 'one_time';
  status: 'active' | 'completed' | 'cancelled' | 'suspended';
  joined_date: string;
  next_payment_due?: string;
  progress_percentage: number;
}

export interface PageProps {
  auth: {
    user: User;
  };
  flash: {
    success?: string;
    error?: string;
  };
}
```

### Dashboard Component
```tsx
// resources/js/Pages/Dashboard/Index.tsx

import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ContributionCard from '@/Components/Contribution/ContributionCard';
import ProjectCard from '@/Components/Project/ProjectCard';
import StatsCard from '@/Components/Dashboard/StatsCard';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { PageProps, Contribution, Project } from '@/Types';

interface DashboardProps extends PageProps {
  contributions: Contribution[];
  availableProjects: Project[];
  summary: {
    total_projects: number;
    total_committed: number;
    total_paid: number;
    total_outstanding: number;
  };
}

export default function Dashboard({ 
  auth, 
  contributions, 
  availableProjects, 
  summary 
}: DashboardProps) {
  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Dashboard" />
      
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Summary Stats */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <StatsCard
              title="Active Projects"
              value={summary.total_projects}
              icon="projects"
              color="blue"
            />
            <StatsCard
              title="Total Committed"
              value={`₦${summary.total_committed.toLocaleString()}`}
              icon="money"
              color="green"
            />
            <StatsCard
              title="Total Paid"
              value={`₦${summary.total_paid.toLocaleString()}`}
              icon="paid"
              color="purple"
            />
            <StatsCard
              title="Outstanding"
              value={`₦${summary.total_outstanding.toLocaleString()}`}
              icon="pending"
              color="orange"
            />
          </div>

          {/* My Contributions */}
          <div className="mb-8">
            <h2 className="text-2xl font-bold text-foreground mb-6">
              My Contributions
            </h2>
            {contributions.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {contributions.map((contribution) => (
                  <ContributionCard
                    key={contribution.id}
                    contribution={contribution}
                  />
                ))}
              </div>
            ) : (
              <Card>
                <CardContent className="text-center py-12">
                  <CardDescription className="mb-4">
                    You haven't joined any projects yet.
                  </CardDescription>
                  <Button asChild>
                    <a href="/projects">Projects</a>
                  </Button>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Available Projects */}
          {availableProjects.length > 0 && (
            <div>
              <h2 className="text-2xl font-bold text-foreground mb-6">
                Available Projects
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {availableProjects.map((project) => (
                  <ProjectCard
                    key={project.id}
                    project={project}
                    showJoinButton
                  />
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

### Project Card Component
```tsx
// resources/js/Components/Project/ProjectCard.tsx

import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Progress } from '@/Components/ui/progress';
import { Badge } from '@/Components/ui/badge';
import { Project } from '@/Types';

interface ProjectCardProps {
  project: Project;
  showJoinButton?: boolean;
}

export default function ProjectCard({ project, showJoinButton = false }: ProjectCardProps) {
  const progressPercentage = project.statistics?.completion_percentage || 0;

  return (
    <Card className="hover:shadow-lg transition-shadow">
      <CardHeader>
        <div className="flex items-start justify-between">
          <CardTitle className="text-xl">{project.name}</CardTitle>
          <Badge variant="secondary">{project.status}</Badge>
        </div>
        <CardDescription className="line-clamp-3">
          {project.description}
        </CardDescription>
      </CardHeader>
      
      <CardContent className="space-y-4">
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Monthly Amount:</span>
            <span className="font-medium">₦{project.monthly_amount.toLocaleString()}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Total Value:</span>
            <span className="font-medium">₦{project.total_project_value.toLocaleString()}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Contributors:</span>
            <span className="font-medium">{project.statistics?.total_contributors || 0}</span>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Progress</span>
            <span className="font-medium">{progressPercentage.toFixed(1)}%</span>
          </div>
          <Progress value={Math.min(progressPercentage, 100)} className="h-2" />
        </div>

        <div className="flex space-x-2">
          <Button variant="outline" className="flex-1" asChild>
            <Link href={`/projects/${project.id}`}>View Details</Link>
          </Button>
          {showJoinButton && (
            <Button className="flex-1" asChild>
              <Link href={`/projects/${project.id}`}>Join Project</Link>
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
```

---

## Inertia.js Configuration

### HandleInertiaRequests Middleware
```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'email_verified_at' => $request->user()->email_verified_at,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],
        ]);
    }
}
```

### shadcn/ui Configuration

#### Installation and Setup
```bash
# Install shadcn/ui CLI
npx shadcn-ui@latest init

# Install required components
npx shadcn-ui@latest add button
npx shadcn-ui@latest add card
npx shadcn-ui@latest add input
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add progress
npx shadcn-ui@latest add badge
npx shadcn-ui@latest add form
npx shadcn-ui@latest add table
npx shadcn-ui@latest add toast
```

#### components.json Configuration
```json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "default",
  "rsc": false,
  "tsx": true,
  "tailwind": {
    "config": "tailwind.config.js",
    "css": "resources/css/app.css",
    "baseColor": "slate",
    "cssVariables": true
  },
  "aliases": {
    "components": "@/Components",
    "utils": "@/lib/utils"
  }
}
```

#### Tailwind CSS Configuration
```javascript
// tailwind.config.js
const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ["class"],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],
    theme: {
        container: {
            center: true,
            padding: "2rem",
            screens: {
                "2xl": "1400px",
            },
        },
        extend: {
            colors: {
                border: "hsl(var(--border))",
                input: "hsl(var(--input))",
                ring: "hsl(var(--ring))",
                background: "hsl(var(--background))",
                foreground: "hsl(var(--foreground))",
                primary: {
                    DEFAULT: "hsl(var(--primary))",
                    foreground: "hsl(var(--primary-foreground))",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary))",
                    foreground: "hsl(var(--secondary-foreground))",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive))",
                    foreground: "hsl(var(--destructive-foreground))",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted))",
                    foreground: "hsl(var(--muted-foreground))",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent))",
                    foreground: "hsl(var(--accent-foreground))",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover))",
                    foreground: "hsl(var(--popover-foreground))",
                },
                card: {
                    DEFAULT: "hsl(var(--card))",
                    foreground: "hsl(var(--card-foreground))",
                },
            },
            borderRadius: {
                lg: "var(--radius)",
                md: "calc(var(--radius) - 2px)",
                sm: "calc(var(--radius) - 4px)",
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                "accordion-down": {
                    from: { height: 0 },
                    to: { height: "var(--radix-accordion-content-height)" },
                },
                "accordion-up": {
                    from: { height: "var(--radix-accordion-content-height)" },
                    to: { height: 0 },
                },
            },
            animation: {
                "accordion-down": "accordion-down 0.2s ease-out",
                "accordion-up": "accordion-up 0.2s ease-out",
            },
        },
    },
    plugins: [
        require('tailwindcss-animate'),
        require('@tailwindcss/forms'),
    ],
};
```

#### CSS Variables (app.css)
```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96%;
    --secondary-foreground: 222.2 84% 4.9%;
    --muted: 210 40% 96%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96%;
    --accent-foreground: 222.2 84% 4.9%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 84% 4.9%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 94.1%;
  }
}

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}
```

### Vite Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});
```

### Custom shadcn/ui Components

#### Form Components for Inertia.js
```tsx
// resources/js/Components/ui/inertia-form.tsx
import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';

interface InertiaFormProps {
  title: string;
  description?: string;
  action: string;
  method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
  children: React.ReactNode;
  submitText?: string;
}

export function InertiaForm({ 
  title, 
  description, 
  action, 
  method = 'post', 
  children, 
  submitText = 'Submit' 
}: InertiaFormProps) {
  const { processing } = useForm();

  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
        {description && <CardDescription>{description}</CardDescription>}
      </CardHeader>
      <CardContent>
        <form action={action} method={method} className="space-y-4">
          {children}
          <Button type="submit" disabled={processing} className="w-full">
            {processing ? 'Processing...' : submitText}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
```

This architecture provides a solid foundation for building the Sannu-Sannu platform with modern development practices, type safety, excellent developer experience, and a consistent, accessible design system using shadcn/ui components while maintaining the simplicity of a monolithic application structure.