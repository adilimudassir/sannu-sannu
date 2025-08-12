import { render, screen, fireEvent } from '@testing-library/react'
import { describe, it, expect, vi } from 'vitest'
import { AuthCard, AuthForm, AuthInput, AuthButton, AuthFormField } from '../index'

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  useForm: () => ({
    data: { email: '', password: '' },
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
    reset: vi.fn(),
  }),
}))

describe('Auth Components', () => {
  describe('AuthCard', () => {
    it('renders with title and description', () => {
      render(
        <AuthCard title="Test Title" description="Test Description">
          <div>Content</div>
        </AuthCard>
      )
      
      expect(screen.getByText('Test Title')).toBeInTheDocument()
      expect(screen.getByText('Test Description')).toBeInTheDocument()
      expect(screen.getByText('Content')).toBeInTheDocument()
    })

    it('renders without title and description', () => {
      render(
        <AuthCard>
          <div>Content Only</div>
        </AuthCard>
      )
      
      expect(screen.getByText('Content Only')).toBeInTheDocument()
    })
  })

  describe('AuthInput', () => {
    it('renders with proper accessibility attributes', () => {
      render(<AuthInput placeholder="Test input" aria-label="Test input" />)
      
      const input = screen.getByLabelText('Test input')
      expect(input).toBeInTheDocument()
      expect(input).toHaveAttribute('placeholder', 'Test input')
    })

    it('applies error styling when error prop is true', () => {
      render(<AuthInput error={true} data-testid="error-input" />)
      
      const input = screen.getByTestId('error-input')
      expect(input).toHaveAttribute('aria-invalid', 'true')
    })
  })

  describe('AuthButton', () => {
    it('renders with children', () => {
      render(<AuthButton>Click me</AuthButton>)
      
      expect(screen.getByText('Click me')).toBeInTheDocument()
    })

    it('shows loading state', () => {
      render(<AuthButton loading={true}>Submit</AuthButton>)
      
      const button = screen.getByRole('button')
      expect(button).toBeDisabled()
      expect(button).toHaveTextContent('Submit')
    })

    it('shows loading text when provided', () => {
      render(<AuthButton loading={true} loadingText="Submitting...">Submit</AuthButton>)
      
      const button = screen.getByRole('button')
      expect(button).toHaveTextContent('Submitting...')
    })

    it('handles click events when not loading', () => {
      const handleClick = vi.fn()
      render(<AuthButton onClick={handleClick}>Click me</AuthButton>)
      
      const button = screen.getByRole('button')
      fireEvent.click(button)
      
      expect(handleClick).toHaveBeenCalledTimes(1)
    })
  })

  describe('AuthFormField', () => {
    it('renders label and children', () => {
      render(
        <AuthFormField label="Email" name="email">
          <input type="email" />
        </AuthFormField>
      )
      
      expect(screen.getByText('Email')).toBeInTheDocument()
      expect(screen.getByRole('textbox')).toBeInTheDocument()
    })

    it('shows required indicator when required', () => {
      render(
        <AuthFormField label="Email" name="email" required>
          <input type="email" />
        </AuthFormField>
      )
      
      expect(screen.getByText('*')).toBeInTheDocument()
    })

    it('displays error message when provided', () => {
      render(
        <AuthFormField label="Email" name="email" error="Email is required">
          <input type="email" />
        </AuthFormField>
      )
      
      expect(screen.getByText('Email is required')).toBeInTheDocument()
    })
  })
})