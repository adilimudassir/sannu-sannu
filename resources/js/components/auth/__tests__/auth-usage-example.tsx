import React from 'react'
import { Head, useForm } from '@inertiajs/react'
import { AuthCard, AuthForm, AuthInput, AuthButton, AuthFormField } from '../index'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'

// Example showing how to use the auth components in a real login form
export function LoginExample() {
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

              <div className="flex items-center space-x-3">
                <Checkbox
                  id="remember"
                  name="remember"
                  checked={form.data.remember}
                  onCheckedChange={(checked) => form.setData('remember', !!checked)}
                />
                <Label htmlFor="remember">Remember me</Label>
              </div>

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

        <div className="text-center text-sm text-muted-foreground">
          Don't have an account?{' '}
          <a href={route('register')} className="text-primary hover:underline">
            Sign up
          </a>
        </div>
      </AuthCard>
    </>
  )
}

// Example showing how to use the auth components in a registration form
export function RegisterExample() {
  return (
    <>
      <Head title="Create Account" />
      
      <AuthCard 
        title="Create your account" 
        description="Enter your information to get started"
      >
        <AuthForm
          initialData={{ name: '', email: '', password: '', password_confirmation: '' }}
          onSubmit={(data, form) => {
            form.post(route('register'))
          }}
        >
          {(form) => (
            <>
              <AuthFormField
                label="Full Name"
                name="name"
                error={form.errors.name}
                required
              >
                <AuthInput
                  type="text"
                  placeholder="John Doe"
                  value={form.data.name}
                  onChange={(e) => form.setData('name', e.target.value)}
                  error={!!form.errors.name}
                  autoComplete="name"
                  autoFocus
                />
              </AuthFormField>

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
                  placeholder="Create a strong password"
                  value={form.data.password}
                  onChange={(e) => form.setData('password', e.target.value)}
                  error={!!form.errors.password}
                  autoComplete="new-password"
                />
              </AuthFormField>

              <AuthFormField
                label="Confirm Password"
                name="password_confirmation"
                error={form.errors.password_confirmation}
                required
              >
                <AuthInput
                  type="password"
                  placeholder="Confirm your password"
                  value={form.data.password_confirmation}
                  onChange={(e) => form.setData('password_confirmation', e.target.value)}
                  error={!!form.errors.password_confirmation}
                  autoComplete="new-password"
                />
              </AuthFormField>

              <AuthButton
                type="submit"
                className="w-full"
                loading={form.processing}
                loadingText="Creating account..."
              >
                Create Account
              </AuthButton>
            </>
          )}
        </AuthForm>

        <div className="text-center text-sm text-muted-foreground">
          Already have an account?{' '}
          <a href={route('login')} className="text-primary hover:underline">
            Sign in
          </a>
        </div>
      </AuthCard>
    </>
  )
}

export default { LoginExample, RegisterExample }