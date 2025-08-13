import { render, screen } from '@testing-library/react'
import { vi, describe, it, beforeEach, expect } from 'vitest'
import Register from '../register'

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
  Link: ({ children, href, ...props }: { children: React.ReactNode; href: string; [key: string]: any }) => 
    <a href={href} {...props}>{children}</a>,
  router: {
    post: vi.fn(),
  },
  useForm: () => ({
    data: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
    reset: vi.fn(),
    hasErrors: false,
  }),
}))

// Mock route helper
global.route = vi.fn((name: string) => {
  const routes: Record<string, string> = {
    'register.store': '/register',
    'login': '/login',
  }
  return routes[name] || `/${name}`
})

describe('Register Page', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders with AuthCard layout', () => {
    render(<Register />)
    
    expect(screen.getByText('Create an account')).toBeInTheDocument()
    expect(screen.getByText('Enter your details below to create your global account')).toBeInTheDocument()
  })

  it('renders all required form fields', () => {
    render(<Register />)
    
    expect(screen.getByPlaceholderText('Full name')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('email@example.com')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Password')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Confirm password')).toBeInTheDocument()
  })

  it('renders submit button with correct text', () => {
    render(<Register />)
    
    const submitButton = screen.getByRole('button', { name: /create account/i })
    expect(submitButton).toBeInTheDocument()
    expect(submitButton).toHaveAttribute('type', 'submit')
  })

  it('renders login link', () => {
    render(<Register />)
    
    expect(screen.getByText(/already have an account/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /log in/i })).toBeInTheDocument()
  })

  it('has proper form accessibility attributes', () => {
    render(<Register />)
    
    const nameInput = screen.getByPlaceholderText('Full name')
    const emailInput = screen.getByPlaceholderText('email@example.com')
    const passwordInput = screen.getByPlaceholderText('Password')
    const confirmPasswordInput = screen.getByPlaceholderText('Confirm password')
    
    expect(nameInput).toHaveAttribute('required')
    expect(emailInput).toHaveAttribute('required')
    expect(passwordInput).toHaveAttribute('required')
    expect(confirmPasswordInput).toHaveAttribute('required')
    
    expect(nameInput).toHaveAttribute('autoComplete', 'name')
    expect(emailInput).toHaveAttribute('autoComplete', 'email')
    expect(passwordInput).toHaveAttribute('autoComplete', 'new-password')
    expect(confirmPasswordInput).toHaveAttribute('autoComplete', 'new-password')
  })

  it('has proper tab order', () => {
    render(<Register />)
    
    expect(screen.getByPlaceholderText('Full name')).toHaveAttribute('tabIndex', '1')
    expect(screen.getByPlaceholderText('email@example.com')).toHaveAttribute('tabIndex', '2')
    expect(screen.getByPlaceholderText('Password')).toHaveAttribute('tabIndex', '3')
    expect(screen.getByPlaceholderText('Confirm password')).toHaveAttribute('tabIndex', '4')
    expect(screen.getByRole('button', { name: /create account/i })).toHaveAttribute('tabIndex', '5')
    expect(screen.getByRole('link', { name: /log in/i })).toHaveAttribute('tabIndex', '6')
  })

  it('shows proper placeholders', () => {
    render(<Register />)
    
    expect(screen.getByPlaceholderText('Full name')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('email@example.com')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Password')).toBeInTheDocument()
    expect(screen.getByPlaceholderText('Confirm password')).toBeInTheDocument()
  })

  it('uses AuthCard component styling', () => {
    render(<Register />)
    
    // Check that the component structure matches AuthCard expectations
    const title = screen.getByText('Create an account')
    const description = screen.getByText('Enter your details below to create your global account')
    
    expect(title).toBeInTheDocument()
    expect(description).toBeInTheDocument()
  })
})