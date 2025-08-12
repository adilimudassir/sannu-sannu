# Auth UI Components

This directory contains reusable authentication UI components designed to work with Laravel Inertia.js and provide a consistent, accessible, and modern authentication experience.

## Components

### AuthCard

A centered card container for all authentication forms with optional title and description.

```tsx
import { AuthCard } from '@/components/auth'

<AuthCard 
  title="Welcome back" 
  description="Enter your credentials to access your account"
>
  {/* Form content */}
</AuthCard>
```

**Props:**
- `title?: string` - Optional title displayed at the top of the card
- `description?: string` - Optional description displayed below the title
- `children: React.ReactNode` - The form content
- `className?: string` - Additional CSS classes
- All other props from the base Card component

### AuthForm

A form wrapper that integrates with Inertia.js forms and provides form state management.

```tsx
import { AuthForm } from '@/components/auth'

<AuthForm
  initialData={{ email: '', password: '' }}
  onSubmit={(data, form) => {
    form.post(route('login'))
  }}
>
  {(form) => (
    // Form fields using the form object
  )}
</AuthForm>
```

**Props:**
- `initialData: TForm` - Initial form data object
- `onSubmit: (data: TForm, form: InertiaFormProps<TForm>) => void` - Form submission handler
- `children: (form: InertiaFormProps<TForm>) => React.ReactNode` - Render function that receives the form object
- `className?: string` - Additional CSS classes

### AuthFormField

A form field wrapper that provides consistent labeling, error display, and accessibility.

```tsx
import { AuthFormField } from '@/components/auth'

<AuthFormField
  label="Email Address"
  name="email"
  error={form.errors.email}
  required
>
  <AuthInput
    type="email"
    value={form.data.email}
    onChange={(e) => form.setData('email', e.target.value)}
  />
</AuthFormField>
```

**Props:**
- `label: string` - Field label text
- `name: string` - Field name (used for htmlFor attribute)
- `error?: string` - Error message to display
- `required?: boolean` - Whether the field is required (shows asterisk)
- `children: React.ReactNode` - The input component
- `className?: string` - Additional CSS classes

### AuthInput

An enhanced input component with proper focus states, error styling, and accessibility features.

```tsx
import { AuthInput } from '@/components/auth'

<AuthInput
  type="email"
  placeholder="email@example.com"
  value={form.data.email}
  onChange={(e) => form.setData('email', e.target.value)}
  error={!!form.errors.email}
  autoComplete="email"
/>
```

**Props:**
- `error?: boolean` - Whether the input has an error (applies error styling)
- All other props from the base Input component

### AuthButton

A button component with loading states and consistent styling for authentication forms.

```tsx
import { AuthButton } from '@/components/auth'

<AuthButton
  type="submit"
  className="w-full"
  loading={form.processing}
  loadingText="Signing in..."
>
  Sign In
</AuthButton>
```

**Props:**
- `loading?: boolean` - Whether the button is in loading state
- `loadingText?: string` - Text to display when loading (optional)
- All other props from the base Button component

## Usage Examples

### Login Form

```tsx
import { Head } from '@inertiajs/react'
import { AuthCard, AuthForm, AuthInput, AuthButton, AuthFormField } from '@/components/auth'

export default function Login() {
  return (
    <>
      <Head title="Log in" />
      
      <AuthCard 
        title="Welcome back" 
        description="Enter your credentials to access your account"
      >
        <AuthForm
          initialData={{ email: '', password: '', remember: false }}
          onSubmit={(data, form) => {
            form.post(route('login'), {
              onFinish: () => form.reset('password'),
            })
          }}
        >
          {(form) => (
            <>
              <AuthFormField
                label="Email Address"
                name="email"
                error={form.errors.email}
                required
              >
                <AuthInput
                  type="email"
                  placeholder="email@example.com"
                  value={form.data.email}
                  onChange={(e) => form.setData('email', e.target.value)}
                  error={!!form.errors.email}
                  autoComplete="email"
                  autoFocus
                />
              </AuthFormField>

              <AuthFormField
                label="Password"
                name="password"
                error={form.errors.password}
                required
              >
                <AuthInput
                  type="password"
                  placeholder="Enter your password"
                  value={form.data.password}
                  onChange={(e) => form.setData('password', e.target.value)}
                  error={!!form.errors.password}
                  autoComplete="current-password"
                />
              </AuthFormField>

              <AuthButton
                type="submit"
                className="w-full"
                loading={form.processing}
                loadingText="Signing in..."
              >
                Sign In
              </AuthButton>
            </>
          )}
        </AuthForm>
      </AuthCard>
    </>
  )
}
```

## Features

- **Accessibility**: All components include proper ARIA attributes, focus management, and keyboard navigation
- **Responsive Design**: Components work well on all screen sizes
- **Error Handling**: Integrated error display with proper styling
- **Loading States**: Built-in loading indicators for form submissions
- **Type Safety**: Full TypeScript support with proper type inference
- **Consistent Styling**: Uses the existing design system and theme
- **Inertia.js Integration**: Seamless integration with Laravel Inertia.js forms

## Requirements Addressed

This implementation addresses the following requirements from the authentication specification:

- **Requirement 1.1**: Centered card layout for authentication forms
- **Requirement 1.5**: Responsive design that works on mobile devices
- **Requirement 1.6**: Proper focus states and accessibility features

## Testing

The components include comprehensive tests covering:
- Rendering with various props
- Accessibility attributes
- Error states
- Loading states
- User interactions

Run tests with:
```bash
npm test -- resources/js/components/auth/__tests__/auth-components.test.tsx
```