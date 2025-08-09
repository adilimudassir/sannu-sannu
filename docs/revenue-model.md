# Revenue Model
## Sannu-Sannu SaaS Platform

### Overview

Sannu-Sannu operates on a **percentage-based revenue sharing model** where the platform charges a small percentage of the total project amounts collected by tenants. This model aligns platform success with tenant success, creating a win-win relationship.

---

## Revenue Model Details

### Core Principle
**"We only make money when you make money"**

The platform charges a percentage fee only on successfully collected contributions, not on project creation or user registration.

### Fee Structure

#### Default Platform Fee
- **Standard Rate**: 5% of total project contributions
- **Minimum Fee**: No minimum fee per transaction
- **Maximum Fee**: No maximum fee cap
- **Payment Timing**: Fees calculated when contributions are successfully collected

#### Fee Calculation Examples

##### Example 1: Small Project
```
Project Amount: ₦100,000
Platform Fee (5%): ₦5,000
Tenant Receives: ₦95,000
```

##### Example 2: Large Project
```
Project Amount: ₦10,000,000
Platform Fee (5%): ₦500,000
Tenant Receives: ₦9,500,000
```

##### Example 3: Multiple Projects
```
Tenant A - 3 Projects:
- Project 1: ₦500,000 → Fee: ₦25,000
- Project 2: ₦1,200,000 → Fee: ₦60,000
- Project 3: ₦800,000 → Fee: ₦40,000
Total Collected: ₦2,500,000
Total Platform Fees: ₦125,000
Tenant Receives: ₦2,375,000
```

---

## Technical Implementation

### Database Schema

#### Platform Fees Tracking
```sql
CREATE TABLE platform_fees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    transaction_id INT UNSIGNED NOT NULL,
    
    -- Fee calculation
    project_amount DECIMAL(12,2) NOT NULL,
    fee_percentage DECIMAL(5,2) NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    
    -- Status tracking
    status ENUM('pending', 'calculated', 'paid') DEFAULT 'pending',
    calculated_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Tenant Fee Configuration
```sql
-- In tenants table
platform_fee_percentage DECIMAL(5,2) DEFAULT 5.00, -- Customizable per tenant
```

### Laravel Implementation

#### Fee Calculation Service
```php
<?php
// app/Services/PlatformFeeService.php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\PlatformFee;

class PlatformFeeService
{
    public function calculateFee(Transaction $transaction): PlatformFee
    {
        $tenant = $transaction->contribution->project->tenant;
        $project = $transaction->contribution->project;
        
        $feePercentage = $tenant->platform_fee_percentage;
        $projectAmount = $project->total_amount;
        $feeAmount = ($projectAmount * $feePercentage) / 100;
        
        return PlatformFee::create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'transaction_id' => $transaction->id,
            'project_amount' => $projectAmount,
            'fee_percentage' => $feePercentage,
            'fee_amount' => $feeAmount,
            'status' => 'calculated',
            'calculated_at' => now(),
        ]);
    }

    public function calculateTotalFees(Tenant $tenant): array
    {
        $fees = PlatformFee::where('tenant_id', $tenant->id)
            ->where('status', 'calculated')
            ->get();

        return [
            'total_project_amount' => $fees->sum('project_amount'),
            'total_fee_amount' => $fees->sum('fee_amount'),
            'average_fee_percentage' => $fees->avg('fee_percentage'),
            'total_projects' => $fees->unique('project_id')->count(),
        ];
    }
}
```

#### Automatic Fee Calculation
```php
<?php
// app/Observers/TransactionObserver.php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\PlatformFeeService;

class TransactionObserver
{
    public function __construct(
        private PlatformFeeService $feeService
    ) {}

    public function updated(Transaction $transaction): void
    {
        // Calculate platform fee when transaction is successful
        if ($transaction->wasChanged('status') && $transaction->status === 'success') {
            $this->feeService->calculateFee($transaction);
        }
    }
}
```

---

## Business Benefits

### For the Platform
1. **Aligned Incentives**: Revenue grows with tenant success
2. **Scalable Model**: No fixed costs to maintain
3. **Low Barrier to Entry**: Tenants can start without upfront costs
4. **Predictable Revenue**: Percentage-based income from all transactions
5. **Growth Potential**: Revenue scales with platform adoption

### For Tenants
1. **No Upfront Costs**: Start using the platform immediately
2. **Pay for Success**: Only pay when projects collect contributions
3. **Transparent Pricing**: Clear percentage-based fee structure
4. **No Hidden Fees**: No setup, monthly, or maintenance charges
5. **Unlimited Usage**: No limits on projects, users, or features

### For Contributors
1. **Transparent Costs**: Fee is built into project amounts
2. **No Additional Charges**: Contributors pay only their contribution amount
3. **Secure Processing**: Platform handles all payment security
4. **Progress Tracking**: Full visibility into contribution progress

---

## Fee Management

### Tenant Dashboard
Tenants can view their fee information through a dedicated dashboard:

```typescript
// Fee summary interface
interface FeeSummary {
  totalProjectAmount: number;
  totalFeeAmount: number;
  feePercentage: number;
  totalProjects: number;
  monthlyBreakdown: {
    month: string;
    projectAmount: number;
    feeAmount: number;
  }[];
}
```

### Fee Reporting
```php
<?php
// app/Http/Controllers/Tenant/FeeController.php

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');
        $fees = PlatformFee::where('tenant_id', $tenant->id)
            ->with(['project', 'transaction'])
            ->when($request->month, function ($query, $month) {
                $query->whereMonth('calculated_at', $month);
            })
            ->when($request->year, function ($query, $year) {
                $query->whereYear('calculated_at', $year);
            })
            ->paginate(20);

        return Inertia::render('Tenant/Fees/Index', [
            'fees' => $fees,
            'summary' => $this->feeService->calculateTotalFees($tenant),
        ]);
    }
}
```

---

## Competitive Advantages

### Compared to Fixed Subscription Models
1. **Lower Risk**: Tenants don't pay if projects don't succeed
2. **Better ROI**: Cost directly correlates with revenue
3. **Scalability**: No need to upgrade plans as usage grows
4. **Simplicity**: One fee structure for all features

### Compared to Transaction-Based Models
1. **Project-Focused**: Fee based on project success, not individual transactions
2. **Predictable**: Percentage is known upfront
3. **Fair**: Large and small projects pay proportionally
4. **Transparent**: No complex fee calculations

---

## Revenue Projections

### Conservative Estimates
```
Assumptions:
- Average project size: ₦1,000,000
- Platform fee: 5%
- Projects per tenant per month: 2
- Active tenants: 100

Monthly Revenue Calculation:
100 tenants × 2 projects × ₦1,000,000 × 5% = ₦10,000,000/month
Annual Revenue: ₦120,000,000
```

### Growth Scenarios

#### Year 1 (Launch)
- **Tenants**: 50
- **Avg Projects/Month**: 1
- **Avg Project Size**: ₦500,000
- **Monthly Revenue**: ₦1,250,000
- **Annual Revenue**: ₦15,000,000

#### Year 2 (Growth)
- **Tenants**: 200
- **Avg Projects/Month**: 2
- **Avg Project Size**: ₦750,000
- **Monthly Revenue**: ₦15,000,000
- **Annual Revenue**: ₦180,000,000

#### Year 3 (Scale)
- **Tenants**: 500
- **Avg Projects/Month**: 3
- **Avg Project Size**: ₦1,000,000
- **Monthly Revenue**: ₦75,000,000
- **Annual Revenue**: ₦900,000,000

---

## Implementation Roadmap

### Phase 1: Core Fee System
- [x] Database schema for platform fees
- [x] Automatic fee calculation on successful transactions
- [x] Basic fee reporting for tenants
- [x] Admin dashboard for fee monitoring

### Phase 2: Advanced Features
- [ ] Custom fee percentages for enterprise tenants
- [ ] Fee payment scheduling and automation
- [ ] Detailed analytics and reporting
- [ ] Fee optimization recommendations

### Phase 3: Enterprise Features
- [ ] Volume-based fee discounts
- [ ] White-label fee management
- [ ] API for fee data integration
- [ ] Advanced financial reporting

This revenue model ensures sustainable growth for both the platform and its tenants while maintaining transparency and fairness in pricing.