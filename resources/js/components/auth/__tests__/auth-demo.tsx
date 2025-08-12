import React from 'react'
import { AuthCard, AuthForm, AuthInput, AuthButton, AuthFormField } from '../index'

// Demo component showing how to use the auth components together
export function AuthDemo() {
  return (
    <AuthCard 
      title="Sign In" 
      description="Enter your credentials to access your account"
    >
      <AuthForm
        initialData={{ email: '', password: '', remember: false }}
        onSubmit={(data, form) => {
          console.log('Form submitted:', data)
          // In real usage, this would call form.post() or similar
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
  )
}

export default AuthDemo