import { render, screen } from '@testing-library/react'
import { vi, describe, it, beforeEach, expect } from 'vitest'
import ForgotPassword from '../forgot-password'
import ResetPassword from '../reset-password'

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  Link: ({ children, href, ...props }: { children: React.ReactNode; href: string; [key: string]: any }) => 
    <a href={href} {...props}>{children}</a>,
  useForm: (initialData?: any) => ({
    data: initialData || { 
      email: '', 
      password: '', 
      password_confirmation: '', 
      token: 'test-token' 
    },
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
    hasErrors: false,
    reset: vi.fn(),
  }),
}))

// Mock route helper
global.route = vi.fn((name: string) => {
  const routes: Record<string, string> = {
    'global.password.email': '/forgot-password',
    'global.password.store': '/reset-password',
    'global.login': '/login',
  }
  return routes[name] || `/${name}`
})

describe('ForgotPassword Page', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders with AuthCard layout', () => {
    render(<ForgotPassword />)
    
    expect(screen.getByText('Forgot password')).toBeInTheDocument()
    expect(screen.getByText('Enter your email address and we\'ll send you a password reset link')).toBeInTheDocument()
  })

  it('renders email input field', () => {
    render(<ForgotPassword />)
    
    expect(screen.getByPlaceholderText('email@example.com')).toBeInTheDocument()
    expect(screen.getByLabelText(/email address/i)).toBeInTheDocument()
  })

  it('renders submit button with correct text', () => {
    render(<ForgotPassword />)
    
    const submitButton = screen.getByRole('button', { name: /email password reset link/i })
    expect(submitButton).toBeInTheDocument()
    expect(submitButton).toHaveAttribute('type', 'submit')
  })

  it('displays status message when provided', () => {
    render(<ForgotPassword status="Reset link sent!" />)
    
    expect(screen.getByText('Reset link sent!')).toBeInTheDocument()
  })

  it('shows back to login link', () => {
    render(<ForgotPassword />)
    
    expect(screen.getByText(/remember your password/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /back to log in/i })).toBeInTheDocument()
  })

  it('has proper form accessibility attributes', () => {
    render(<ForgotPassword />)
    
    const emailInput = screen.getByPlaceholderText('email@example.com')
    
    expect(emailInput).toHaveAttribute('required')
    expect(emailInput).toHaveAttribute('autoComplete', 'email')
    expect(emailInput).toHaveAttribute('type', 'email')
  })

  it('has proper tab order', () => {
    render(<ForgotPassword />)
    
    expect(screen.getByPlaceholderText('email@example.com')).toHaveAttribute('tabIndex', '1')
    expect(screen.getByRole('button', { name: /email password reset link/i })).toHaveAttribute('tabIndex', '2')
    expect(screen.getByRole('link', { name: /back to log in/i })).toHaveAttribute('tabIndex', '3')
  })
})

describe('ResetPassword Page', () => {
  const defaultProps = {
    token: 'test-token',
    email: 'test@example.com',
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders with AuthCard layout', () => {
    render(<ResetPassword {...defaultProps} />)
    
    // Use getAllByText to handle multiple elements with same text
    const resetPasswordElements = screen.getAllByText('Reset password')
    expect(resetPasswordElements.length).toBeGreaterThan(0)
    expect(screen.getByText('Please enter your new password below')).toBeInTheDocument()
  })

  it('renders all required form fields', () => {
    render(<ResetPassword {...defaultProps} />)
    
    expect(screen.getByLabelText(/email address/i)).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Enter your new password')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Confirm your new password')).toBeInTheDocument()
  })

  it('renders submit button with correct text', () => {
    render(<ResetPassword {...defaultProps} />)
    
    const submitButton = screen.getByRole('button', { name: /reset password/i })
    expect(submitButton).toBeInTheDocument()
    expect(submitButton).toHaveAttribute('type', 'submit')
  })

  it('shows email field as readonly', () => {
    render(<ResetPassword {...defaultProps} />)
    
    const emailInput = screen.getByLabelText(/email address/i)
    expect(emailInput).toHaveAttribute('readonly')
    // Note: The email value is set by the form data, not the props directly
  })

  it('has proper form accessibility attributes', () => {
    render(<ResetPassword {...defaultProps} />)
    
    const emailInput = screen.getByLabelText(/email address/i)
    const passwordInput = screen.getByPlaceholderText('Enter your new password')
    const confirmPasswordInput = screen.getByPlaceholderText('Confirm your new password')
    
    expect(emailInput).toHaveAttribute('autoComplete', 'email')
    expect(passwordInput).toHaveAttribute('required')
    expect(confirmPasswordInput).toHaveAttribute('required')
    expect(passwordInput).toHaveAttribute('autoComplete', 'new-password')
    expect(confirmPasswordInput).toHaveAttribute('autoComplete', 'new-password')
    expect(passwordInput).toHaveAttribute('type', 'password')
    expect(confirmPasswordInput).toHaveAttribute('type', 'password')
  })

  it('has proper tab order', () => {
    render(<ResetPassword {...defaultProps} />)
    
    expect(screen.getByLabelText(/email address/i)).toHaveAttribute('tabIndex', '1')
    expect(screen.getByPlaceholderText('Enter your new password')).toHaveAttribute('tabIndex', '2')
    expect(screen.getByPlaceholderText('Confirm your new password')).toHaveAttribute('tabIndex', '3')
    expect(screen.getByRole('button', { name: /reset password/i })).toHaveAttribute('tabIndex', '4')
  })
})