# Simple Theming System

## Sannu-Sannu SaaS Platform

### Overview

The Sannu-Sannu platform uses a simple, code-based theming system where all tenants share the same color scheme. Colors are defined in configuration files and CSS custom properties, making it easy to update the entire platform's appearance by changing values in the codebase.

---

## Theme Architecture

### Core Concepts

1. **CSS Custom Properties**: All colors are defined as CSS variables
2. **Code-Based Configuration**: Colors defined in config files and CSS
3. **Global Theme**: All tenants use the same color scheme
4. **shadcn/ui Integration**: All components automatically inherit theme colors
5. **Easy Customization**: Change colors by updating configuration files

---

## Implementation

### 1. Theme Configuration File

```typescript
// resources/js/config/theme.ts
export const themeConfig = {
  // Brand colors (customize these for your brand)
  brand: {
    primary: "#3B82F6", // Blue - Main brand color
    secondary: "#10B981", // Green - Secondary brand color
    accent: "#8B5CF6", // Purple - Accent color
  },

  // Semantic colors
  semantic: {
    success: "#10B981", // Green - Success states
    warning: "#F59E0B", // Amber - Warning states
    error: "#EF4444", // Red - Error states
    info: "#3B82F6", // Blue - Info states
  },

  // UI settings
  borderRadius: "0.5rem",

  // Light/dark mode support
  modes: {
    light: {
      background: "#FFFFFF",
      foreground: "#0F172A",
      muted: "#F1F5F9",
      mutedForeground: "#64748B",
      border: "#E2E8F0",
      input: "#E2E8F0",
    },
    dark: {
      background: "#0F172A",
      foreground: "#F8FAFC",
      muted: "#1E293B",
      mutedForeground: "#94A3B8",
      border: "#334155",
      input: "#334155",
    },
  },
} as const;

export type ThemeConfig = typeof themeConfig;
```

### 2. CSS Variables

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    /* Light mode colors */
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --muted: 210 40% 96%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    /* Dark mode colors */
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
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

### 3. Tailwind CSS Configuration

```javascript
// tailwind.config.js
const { themeConfig } = require("./resources/js/config/theme");

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  content: ["./resources/views/**/*.blade.php", "./resources/js/**/*.tsx"],
  theme: {
    extend: {
      colors: {
        // Brand colors from config
        primary: {
          DEFAULT: themeConfig.brand.primary,
          foreground: "#FFFFFF",
        },
        secondary: {
          DEFAULT: themeConfig.brand.secondary,
          foreground: "#FFFFFF",
        },
        accent: {
          DEFAULT: themeConfig.brand.accent,
          foreground: "#FFFFFF",
        },

        // Semantic colors from config
        success: {
          DEFAULT: themeConfig.semantic.success,
          foreground: "#FFFFFF",
        },
        warning: {
          DEFAULT: themeConfig.semantic.warning,
          foreground: "#FFFFFF",
        },
        destructive: {
          DEFAULT: themeConfig.semantic.error,
          foreground: "#FFFFFF",
        },

        // UI colors (CSS variables for light/dark mode)
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        muted: "hsl(var(--muted))",
        mutedForeground: "hsl(var(--muted-foreground))",
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
      },
      borderRadius: {
        lg: themeConfig.borderRadius,
        md: `calc(${themeConfig.borderRadius} - 2px)`,
        sm: `calc(${themeConfig.borderRadius} - 4px)`,
      },
    },
  },
  plugins: [require("tailwindcss-animate"), require("@tailwindcss/forms")],
};
```

### 4. Dark Mode Toggle (Optional)

```tsx
// resources/js/Components/ThemeToggle.tsx
import React, { useEffect, useState } from "react";
import { Button } from "@/Components/ui/button";
import { Moon, Sun } from "lucide-react";

export function ThemeToggle() {
  const [isDark, setIsDark] = useState(false);

  useEffect(() => {
    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem("theme");
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)"
    ).matches;

    if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
      setIsDark(true);
      document.documentElement.classList.add("dark");
    }
  }, []);

  const toggleTheme = () => {
    const newTheme = !isDark;
    setIsDark(newTheme);

    if (newTheme) {
      document.documentElement.classList.add("dark");
      localStorage.setItem("theme", "dark");
    } else {
      document.documentElement.classList.remove("dark");
      localStorage.setItem("theme", "light");
    }
  };

  return (
    <Button
      variant="outline"
      size="icon"
      onClick={toggleTheme}
      className="h-9 w-9"
    >
      {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
      <span className="sr-only">Toggle theme</span>
    </Button>
  );
}
```

---

## Usage Examples

### Using Brand Colors in Components

```tsx
// Using configured brand colors
<Button className="bg-primary text-primary-foreground">
  Primary Action
</Button>

<Button className="bg-secondary text-secondary-foreground">
  Secondary Action
</Button>

<div className="bg-success text-success-foreground p-4 rounded">
  Success message
</div>

<div className="bg-warning text-warning-foreground p-4 rounded">
  Warning message
</div>
```

### Using Semantic Colors

```tsx
// Status indicators
<Badge className="bg-success text-success-foreground">Active</Badge>
<Badge className="bg-warning text-warning-foreground">Pending</Badge>
<Badge className="bg-destructive text-destructive-foreground">Failed</Badge>

// Alert components
<Alert className="border-success bg-success/10">
  <CheckCircle className="h-4 w-4 text-success" />
  <AlertTitle className="text-success">Success</AlertTitle>
  <AlertDescription>Operation completed successfully.</AlertDescription>
</Alert>
```

### Using UI Colors (Light/Dark Mode)

```tsx
// These automatically adapt to light/dark mode
<Card className="bg-background border-border">
  <CardHeader>
    <CardTitle className="text-foreground">Title</CardTitle>
    <CardDescription className="text-muted-foreground">
      Description text
    </CardDescription>
  </CardHeader>
  <CardContent className="text-foreground">Content goes here</CardContent>
</Card>
```

---

## Customization Guide

### Changing Brand Colors

To update the platform's brand colors, simply modify the `themeConfig` object:

```typescript
// resources/js/config/theme.ts
export const themeConfig = {
  brand: {
    primary: "#FF6B35", // Change to orange
    secondary: "#004E89", // Change to navy blue
    accent: "#9A031E", // Change to dark red
  },
  // ... rest of config
};
```

### Adding New Colors

```typescript
// Add new semantic colors
export const themeConfig = {
  // ... existing config
  semantic: {
    success: "#10B981",
    warning: "#F59E0B",
    error: "#EF4444",
    info: "#3B82F6",
    // Add new colors
    neutral: "#6B7280",
    premium: "#7C3AED",
  },
};
```

Then update Tailwind config:

```javascript
// tailwind.config.js
colors: {
  // ... existing colors
  neutral: {
    DEFAULT: themeConfig.semantic.neutral,
    foreground: '#FFFFFF',
  },
  premium: {
    DEFAULT: themeConfig.semantic.premium,
    foreground: '#FFFFFF',
  },
}
```

### Updating Border Radius

```typescript
// For more rounded corners
export const themeConfig = {
  borderRadius: "0.75rem", // Increase from 0.5rem
  // ... rest of config
};
```

---

## Benefits of This Approach

### Simplicity

- **No Database Complexity**: Colors are just configuration files
- **Easy Updates**: Change colors by updating code and redeploying
- **Consistent**: All tenants use the same, professional color scheme
- **Fast**: No database queries for theme data

### Developer Experience

- **Type Safety**: TypeScript ensures color values are valid
- **IntelliSense**: Auto-completion for color names
- **Hot Reload**: Changes appear instantly during development
- **Version Control**: Color changes are tracked in git

### Performance

- **No Runtime Overhead**: Colors are compiled at build time
- **CSS Variables**: Efficient light/dark mode switching
- **Cached**: Colors are part of the CSS bundle
- **Fast Loading**: No additional network requests

### Maintenance

- **Single Source of Truth**: All colors defined in one place
- **Easy Branding**: Update brand colors across entire platform
- **Consistent Design**: Ensures visual consistency across all tenants
- **Future-Proof**: Easy to extend with new colors or themes

This simple theming system provides all the benefits of a customizable design system while maintaining simplicity and performance.
