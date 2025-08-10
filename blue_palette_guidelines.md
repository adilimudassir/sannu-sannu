# Modern Minimalist Blue Design System

This document provides **comprehensive design and implementation guidelines** for creating a cohesive, accessible, and modern user interface using the **Modern Minimalist Blue Palette**.

---

## 🎨 Color Palette & Semantic Usage

| Name              | Hex Code  | RGB           | HSL           | Usage & Context |
|-------------------|-----------|---------------|---------------|-----------------|
| **Deep Navy**     | `#0D1B2A` | 13, 27, 42    | 218°, 53%, 11% | Primary brand, headers, high-contrast backgrounds |
| **Royal Blue**    | `#1B263B` | 27, 38, 59    | 219°, 37%, 17% | Navigation, secondary sections, card headers |
| **Modern Sky**    | `#415A77` | 65, 90, 119   | 212°, 29%, 36% | Interactive elements, buttons, active states |
| **Cool Steel**    | `#778DA9` | 119, 141, 169 | 213°, 23%, 56% | Borders, icons, muted text, disabled states |
| **Soft Ice**      | `#E0E1DD` | 224, 225, 221 | 60°, 7%, 87%   | Page backgrounds, subtle dividers |

### Extended Palette for UI States

| State             | Color     | Hex Code  | Usage |
|-------------------|-----------|-----------|-------|
| **Success**       | Green     | `#10B981` | Success messages, completed states |
| **Warning**       | Amber     | `#F59E0B` | Warning alerts, pending states |
| **Error**         | Red       | `#EF4444` | Error messages, destructive actions |
| **Info**          | Blue      | `#3B82F6` | Information alerts, neutral actions |

---

## 🎯 Design Principles

### 1. Hierarchy & Contrast
- **Primary actions**: Deep Navy or Modern Sky backgrounds
- **Secondary actions**: Cool Steel borders with transparent backgrounds
- **Text hierarchy**: Deep Navy for headings, Cool Steel for body text
- **Minimum contrast ratio**: 4.5:1 for normal text, 3:1 for large text

### 2. Spacing & Layout
- **Base unit**: 4px (0.25rem)
- **Component spacing**: 8px, 12px, 16px, 24px, 32px, 48px
- **Container max-width**: 1200px with responsive breakpoints
- **Grid system**: 12-column grid with 24px gutters

### 3. Typography Scale
```css
/* Font sizes following 1.25 ratio (Major Third) */
--text-xs: 0.75rem;    /* 12px */
--text-sm: 0.875rem;   /* 14px */
--text-base: 1rem;     /* 16px */
--text-lg: 1.125rem;   /* 18px */
--text-xl: 1.25rem;    /* 20px */
--text-2xl: 1.5rem;    /* 24px */
--text-3xl: 1.875rem;  /* 30px */
--text-4xl: 2.25rem;   /* 36px */
```

### 4. Border Radius & Shadows
```css
--radius-sm: 0.25rem;   /* 4px - small elements */
--radius-md: 0.375rem;  /* 6px - buttons, inputs */
--radius-lg: 0.5rem;    /* 8px - cards, modals */
--radius-xl: 0.75rem;   /* 12px - large containers */

--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
```

---

## � Foprm Design Guidelines

### Input Fields
```css
.input-field {
  background: white;
  border: 1px solid var(--cool-steel);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  font-size: var(--text-base);
  transition: all 0.2s ease;
}

.input-field:focus {
  outline: none;
  border-color: var(--modern-sky);
  box-shadow: 0 0 0 3px rgba(65, 90, 119, 0.1);
}

.input-field:disabled {
  background: var(--soft-ice);
  color: var(--cool-steel);
  cursor: not-allowed;
}
```

### Form Layout Standards
- **Label positioning**: Above input fields with 8px spacing
- **Field spacing**: 24px between form groups
- **Required indicators**: Red asterisk (*) after label text
- **Help text**: 12px font size, Cool Steel color, 4px below input
- **Error states**: Red border, red text, error icon

### Button Hierarchy
```css
/* Primary Button */
.btn-primary {
  background: var(--modern-sky);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius-md);
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: var(--deep-navy);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

/* Secondary Button */
.btn-secondary {
  background: transparent;
  color: var(--modern-sky);
  border: 1px solid var(--modern-sky);
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius-md);
}

/* Ghost Button */
.btn-ghost {
  background: transparent;
  color: var(--cool-steel);
  border: none;
  padding: 0.75rem 1rem;
}
```

---

## 📊 Table Design Standards

### Table Structure
```css
.table-container {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.table-header {
  background: var(--soft-ice);
  border-bottom: 1px solid var(--cool-steel);
}

.table-header th {
  padding: 1rem;
  font-weight: 600;
  color: var(--deep-navy);
  text-align: left;
  font-size: var(--text-sm);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-row {
  border-bottom: 1px solid rgba(119, 141, 169, 0.1);
  transition: background-color 0.2s ease;
}

.table-row:hover {
  background: rgba(224, 225, 221, 0.3);
}

.table-cell {
  padding: 1rem;
  color: var(--deep-navy);
  vertical-align: middle;
}
```

### Table Patterns
- **Zebra striping**: Alternate row backgrounds using `rgba(224, 225, 221, 0.2)`
- **Sortable headers**: Add arrow icons, hover states
- **Action columns**: Right-aligned, use icon buttons
- **Status indicators**: Colored badges with semantic colors
- **Pagination**: Bottom-aligned, Modern Sky for active page

---

## 🎴 Card & Container Guidelines

### Card Components
```css
.card {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: 1.5rem;
  border: 1px solid rgba(119, 141, 169, 0.1);
  transition: all 0.2s ease;
}

.card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.card-header {
  border-bottom: 1px solid var(--soft-ice);
  padding-bottom: 1rem;
  margin-bottom: 1rem;
}

.card-title {
  color: var(--deep-navy);
  font-size: var(--text-xl);
  font-weight: 600;
  margin: 0;
}
```

### Layout Containers
- **Page container**: Max-width 1200px, centered with horizontal padding
- **Section spacing**: 48px between major sections
- **Card grids**: 24px gap between cards
- **Sidebar width**: 280px with 24px padding

---

## 🔔 Alert & Notification Design

### Alert Components
```css
.alert {
  padding: 1rem 1.25rem;
  border-radius: var(--radius-md);
  border-left: 4px solid;
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.alert-success {
  background: rgba(16, 185, 129, 0.1);
  border-left-color: #10B981;
  color: #065F46;
}

.alert-warning {
  background: rgba(245, 158, 11, 0.1);
  border-left-color: #F59E0B;
  color: #92400E;
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border-left-color: #EF4444;
  color: #991B1B;
}

.alert-info {
  background: rgba(65, 90, 119, 0.1);
  border-left-color: var(--modern-sky);
  color: var(--deep-navy);
}
```

---

## 🎨 Implementation Examples

### shadcn/ui Installation & Setup

#### Installation Commands
```bash
# Install shadcn/ui CLI
npx shadcn-ui@latest init

# Install required components for Sannu-Sannu
npx shadcn-ui@latest add button
npx shadcn-ui@latest add card
npx shadcn-ui@latest add input
npx shadcn-ui@latest add label
npx shadcn-ui@latest add table
npx shadcn-ui@latest add alert
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add progress
npx shadcn-ui@latest add badge
npx shadcn-ui@latest add form
npx shadcn-ui@latest add toast
npx shadcn-ui@latest add tabs
npx shadcn-ui@latest add select
npx shadcn-ui@latest add dropdown-menu
npx shadcn-ui@latest add avatar
npx shadcn-ui@latest add separator
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
    "cssVariables": true,
    "prefix": ""
  },
  "aliases": {
    "components": "@/Components",
    "utils": "@/lib/utils"
  }
}
```

### CSS Custom Properties (Legacy Support)
```css
:root {
  /* Color Palette - Legacy CSS Variables */
  --deep-navy: #0D1B2A;
  --royal-blue: #1B263B;
  --modern-sky: #415A77;
  --cool-steel: #778DA9;
  --soft-ice: #E0E1DD;
  
  /* Semantic Colors */
  --success: #10B981;
  --warning: #F59E0B;
  --error: #EF4444;
  --info: #3B82F6;
  
  /* Typography */
  --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-weight-normal: 400;
  --font-weight-medium: 500;
  --font-weight-semibold: 600;
  --font-weight-bold: 700;
  
  /* Spacing Scale */
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-12: 3rem;
  
  /* Border Radius */
  --radius-sm: 0.25rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --radius-xl: 0.75rem;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  
  /* Transitions */
  --transition-fast: 0.15s ease;
  --transition-base: 0.2s ease;
  --transition-slow: 0.3s ease;
}
```

### Tailwind CSS 4 Configuration
```js
// tailwind.config.js - Tailwind CSS 4 compatible
import { type Config } from 'tailwindcss'

export default {
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './resources/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        // shadcn/ui compatible color system
        border: 'hsl(var(--border))',
        input: 'hsl(var(--input))',
        ring: 'hsl(var(--ring))',
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
          50: '#f0f4f8',
          100: '#d9e2ec',
          200: '#bcccdc',
          300: '#9fb3c8',
          400: '#829ab1',
          500: '#415A77', // Modern Sky - maps to hsl(212, 29%, 36%)
          600: '#334155',
          700: '#1B263B', // Royal Blue - maps to hsl(219, 37%, 17%)
          800: '#0f172a',
          900: '#0D1B2A', // Deep Navy - maps to hsl(218, 53%, 11%)
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary))',
          foreground: 'hsl(var(--secondary-foreground))',
        },
        destructive: {
          DEFAULT: 'hsl(var(--destructive))',
          foreground: 'hsl(var(--destructive-foreground))',
        },
        muted: {
          DEFAULT: 'hsl(var(--muted))',
          foreground: 'hsl(var(--muted-foreground))',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent))',
          foreground: 'hsl(var(--accent-foreground))',
        },
        popover: {
          DEFAULT: 'hsl(var(--popover))',
          foreground: 'hsl(var(--popover-foreground))',
        },
        card: {
          DEFAULT: 'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
        },
        // Custom blue palette
        'deep-navy': '#0D1B2A',
        'royal-blue': '#1B263B',
        'modern-sky': '#415A77',
        'cool-steel': '#778DA9',
        'soft-ice': '#E0E1DD',
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      keyframes: {
        'accordion-down': {
          from: { height: '0' },
          to: { height: 'var(--radix-accordion-content-height)' },
        },
        'accordion-up': {
          from: { height: 'var(--radix-accordion-content-height)' },
          to: { height: '0' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'slide-up': {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
      animation: {
        'accordion-down': 'accordion-down 0.2s ease-out',
        'accordion-up': 'accordion-up 0.2s ease-out',
        'fade-in': 'fade-in 0.2s ease-in-out',
        'slide-up': 'slide-up 0.3s ease-out',
      },
    },
  },
  plugins: [require('tailwindcss-animate')],
} satisfies Config
```

### shadcn/ui CSS Variables
```css
/* globals.css - shadcn/ui compatible CSS variables */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    /* shadcn/ui base variables */
    --background: 0 0% 100%;
    --foreground: 218 53% 11%; /* Deep Navy */
    --card: 0 0% 100%;
    --card-foreground: 218 53% 11%;
    --popover: 0 0% 100%;
    --popover-foreground: 218 53% 11%;
    --primary: 212 29% 36%; /* Modern Sky */
    --primary-foreground: 0 0% 100%;
    --secondary: 60 7% 87%; /* Soft Ice */
    --secondary-foreground: 218 53% 11%;
    --muted: 60 7% 87%;
    --muted-foreground: 213 23% 56%; /* Cool Steel */
    --accent: 60 7% 87%;
    --accent-foreground: 218 53% 11%;
    --destructive: 0 84% 60%;
    --destructive-foreground: 0 0% 100%;
    --border: 213 23% 56%; /* Cool Steel */
    --input: 213 23% 56%;
    --ring: 212 29% 36%; /* Modern Sky */
    --radius: 0.5rem;
  }

  .dark {
    --background: 218 53% 11%; /* Deep Navy */
    --foreground: 0 0% 100%;
    --card: 219 37% 17%; /* Royal Blue */
    --card-foreground: 0 0% 100%;
    --popover: 219 37% 17%;
    --popover-foreground: 0 0% 100%;
    --primary: 212 29% 36%; /* Modern Sky */
    --primary-foreground: 0 0% 100%;
    --secondary: 219 37% 17%;
    --secondary-foreground: 0 0% 100%;
    --muted: 219 37% 17%;
    --muted-foreground: 213 23% 56%;
    --accent: 219 37% 17%;
    --accent-foreground: 0 0% 100%;
    --destructive: 0 62% 30%;
    --destructive-foreground: 0 0% 100%;
    --border: 219 37% 17%;
    --input: 219 37% 17%;
    --ring: 212 29% 36%;
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

### shadcn/ui Component Examples

#### Button Component (shadcn/ui compatible)
```tsx
// components/ui/button.tsx - shadcn/ui Button component
import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"
import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground hover:bg-primary/90",
        destructive: "bg-destructive text-destructive-foreground hover:bg-destructive/90",
        outline: "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
        secondary: "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm: "h-9 rounded-md px-3",
        lg: "h-11 rounded-md px-8",
        icon: "h-10 w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button"
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      />
    )
  }
)
Button.displayName = "Button"

export { Button, buttonVariants }
```

#### Card Component (shadcn/ui compatible)
```tsx
// components/ui/card.tsx - shadcn/ui Card component
import * as React from "react"
import { cn } from "@/lib/utils"

const Card = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn(
      "rounded-lg border bg-card text-card-foreground shadow-sm",
      className
    )}
    {...props}
  />
))
Card.displayName = "Card"

const CardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-6", className)}
    {...props}
  />
))
CardHeader.displayName = "CardHeader"

const CardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn(
      "text-2xl font-semibold leading-none tracking-tight",
      className
    )}
    {...props}
  />
))
CardTitle.displayName = "CardTitle"

const CardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-sm text-muted-foreground", className)}
    {...props}
  />
))
CardDescription.displayName = "CardDescription"

const CardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-6 pt-0", className)} {...props} />
))
CardContent.displayName = "CardContent"

const CardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center p-6 pt-0", className)}
    {...props}
  />
))
CardFooter.displayName = "CardFooter"

export { Card, CardHeader, CardFooter, CardTitle, CardDescription, CardContent }
```

#### Input Component (shadcn/ui compatible)
```tsx
// components/ui/input.tsx - shadcn/ui Input component
import * as React from "react"
import { cn } from "@/lib/utils"

export interface InputProps
  extends React.InputHTMLAttributes<HTMLInputElement> {}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        className={cn(
          "flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50",
          className
        )}
        ref={ref}
        {...props}
      />
    )
  }
)
Input.displayName = "Input"

export { Input }
```

#### Table Component (shadcn/ui compatible)
```tsx
// components/ui/table.tsx - shadcn/ui Table component
import * as React from "react"
import { cn } from "@/lib/utils"

const Table = React.forwardRef<
  HTMLTableElement,
  React.HTMLAttributes<HTMLTableElement>
>(({ className, ...props }, ref) => (
  <div className="relative w-full overflow-auto">
    <table
      ref={ref}
      className={cn("w-full caption-bottom text-sm", className)}
      {...props}
    />
  </div>
))
Table.displayName = "Table"

const TableHeader = React.forwardRef<
  HTMLTableSectionElement,
  React.HTMLAttributes<HTMLTableSectionElement>
>(({ className, ...props }, ref) => (
  <thead ref={ref} className={cn("[&_tr]:border-b", className)} {...props} />
))
TableHeader.displayName = "TableHeader"

const TableBody = React.forwardRef<
  HTMLTableSectionElement,
  React.HTMLAttributes<HTMLTableSectionElement>
>(({ className, ...props }, ref) => (
  <tbody
    ref={ref}
    className={cn("[&_tr:last-child]:border-0", className)}
    {...props}
  />
))
TableBody.displayName = "TableBody"

const TableRow = React.forwardRef<
  HTMLTableRowElement,
  React.HTMLAttributes<HTMLTableRowElement>
>(({ className, ...props }, ref) => (
  <tr
    ref={ref}
    className={cn(
      "border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted",
      className
    )}
    {...props}
  />
))
TableRow.displayName = "TableRow"

const TableHead = React.forwardRef<
  HTMLTableCellElement,
  React.ThHTMLAttributes<HTMLTableCellElement>
>(({ className, ...props }, ref) => (
  <th
    ref={ref}
    className={cn(
      "h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0",
      className
    )}
    {...props}
  />
))
TableHead.displayName = "TableHead"

const TableCell = React.forwardRef<
  HTMLTableCellElement,
  React.TdHTMLAttributes<HTMLTableCellElement>
>(({ className, ...props }, ref) => (
  <td
    ref={ref}
    className={cn("p-4 align-middle [&:has([role=checkbox])]:pr-0", className)}
    {...props}
  />
))
TableCell.displayName = "TableCell"

export {
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
}
```

#### Alert Component (shadcn/ui compatible)
```tsx
// components/ui/alert.tsx - shadcn/ui Alert component
import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { cn } from "@/lib/utils"

const alertVariants = cva(
  "relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground",
  {
    variants: {
      variant: {
        default: "bg-background text-foreground",
        destructive:
          "border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive",
        warning: "border-yellow-500/50 text-yellow-600 dark:border-yellow-500 [&>svg]:text-yellow-600",
        success: "border-green-500/50 text-green-600 dark:border-green-500 [&>svg]:text-green-600",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

const Alert = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & VariantProps<typeof alertVariants>
>(({ className, variant, ...props }, ref) => (
  <div
    ref={ref}
    role="alert"
    className={cn(alertVariants({ variant }), className)}
    {...props}
  />
))
Alert.displayName = "Alert"

const AlertTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h5
    ref={ref}
    className={cn("mb-1 font-medium leading-none tracking-tight", className)}
    {...props}
  />
))
AlertTitle.displayName = "AlertTitle"

const AlertDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("text-sm [&_p]:leading-relaxed", className)}
    {...props}
  />
))
AlertDescription.displayName = "AlertDescription"

export { Alert, AlertTitle, AlertDescription }
```

---

## ♿ Accessibility Guidelines

### Color Contrast Requirements
- **Normal text**: Minimum 4.5:1 contrast ratio
- **Large text** (18px+ or 14px+ bold): Minimum 3:1 contrast ratio
- **Interactive elements**: Minimum 3:1 contrast ratio for borders/backgrounds

### Focus Management
- **Focus indicators**: 2px solid Modern Sky outline with 2px offset
- **Focus order**: Logical tab sequence following visual layout
- **Skip links**: Provide skip-to-content links for keyboard users

### Screen Reader Support
- **Semantic HTML**: Use proper heading hierarchy (h1-h6)
- **ARIA labels**: Provide descriptive labels for interactive elements
- **Alt text**: Descriptive alternative text for images
- **Form labels**: Explicit labels for all form inputs

---

## 📱 Responsive Design Breakpoints

```css
/* Mobile First Approach */
/* xs: 0px - 475px */
/* sm: 476px - 640px */
/* md: 641px - 768px */
/* lg: 769px - 1024px */
/* xl: 1025px - 1280px */
/* 2xl: 1281px+ */

@media (min-width: 640px) {
  .container { padding: 0 2rem; }
}

@media (min-width: 768px) {
  .grid-responsive { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
  .grid-responsive { grid-template-columns: repeat(3, 1fr); }
}
```

---

## 🎯 Component Specifications

### Navigation
- **Height**: 64px on desktop, 56px on mobile
- **Background**: Deep Navy with 95% opacity backdrop blur
- **Logo**: 32px height, left-aligned with 24px margin
- **Menu items**: 16px font size, 500 weight, 24px padding

### Modals & Overlays
- **Backdrop**: rgba(13, 27, 42, 0.8) with backdrop blur
- **Modal width**: Max 500px with 24px margin on mobile
- **Animation**: Fade in backdrop, slide up modal content
- **Close button**: Top-right, 24px from edges

### Loading States
- **Skeleton screens**: Use Soft Ice background with shimmer animation
- **Spinners**: Modern Sky color, 24px size for buttons, 48px for page loading
- **Progress bars**: Modern Sky fill, Cool Steel background

---

## ⚠️ Best Practices & Guidelines

### Do's
✅ **Use consistent spacing** from the 4px base unit scale  
✅ **Maintain color hierarchy** with Deep Navy for primary, Cool Steel for secondary  
✅ **Apply hover states** to all interactive elements  
✅ **Use semantic HTML** elements for better accessibility  
✅ **Test color contrast** ratios for all text combinations  
✅ **Implement focus indicators** for keyboard navigation  

### Don'ts
❌ **Don't use more than 3 colors** from the palette in a single component  
❌ **Don't create custom spacing** outside the defined scale  
❌ **Don't use color alone** to convey important information  
❌ **Don't ignore mobile breakpoints** in responsive design  
❌ **Don't use low contrast** color combinations  
❌ **Don't forget loading and error states** in interactive components  

---

## 📋 Design Checklist

### Before Implementation
- [ ] Color contrast ratios meet WCAG AA standards
- [ ] Component follows spacing scale guidelines
- [ ] Interactive states (hover, focus, active) are defined
- [ ] Mobile responsiveness is considered
- [ ] Loading and error states are designed

### During Development
- [ ] Semantic HTML structure is used
- [ ] ARIA labels are provided where needed
- [ ] Focus management is implemented
- [ ] Animations respect user preferences (prefers-reduced-motion)
- [ ] Component is tested with keyboard navigation

### Quality Assurance
- [ ] Visual design matches specifications
- [ ] All interactive elements are accessible
- [ ] Component works across different screen sizes
- [ ] Performance impact is minimal
- [ ] Browser compatibility is verified

---

**Author:** Design System Team  
**Version:** 2.0  
**Last Updated:** 2025-01-10  
**Next Review:** 2025-04-10
