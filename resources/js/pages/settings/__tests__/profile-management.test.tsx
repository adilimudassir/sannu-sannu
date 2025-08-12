import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Profile from '../profile';

// Mock Inertia
const mockUseForm = vi.fn();
const mockUsePage = vi.fn();
const mockRoute = vi.fn();

// Mock the global route function
(global as any).route = mockRoute;

vi.mock('@inertiajs/react', () => ({
  Head: ({ children, title }: any) => <div data-testid="head" title={title}>{children}</div>,
  Link: ({ children, href, ...props }: any) => <a href={href} {...props}>{children}</a>,
  useForm: () => mockUseForm(),
  usePage: () => mockUsePage(),
}));

// Mock components
vi.mock('@/layouts/app-layout', () => ({
  default: ({ children }: any) => <div data-testid="app-layout">{children}</div>,
}));

vi.mock('@/layouts/settings/layout', () => ({
  default: ({ children }: any) => <div data-testid="settings-layout">{children}</div>,
}));

vi.mock('@/components/delete-user', () => ({
  default: () => <div data-testid="delete-user">Delete User Component</div>,
}));

describe('Profile Management', () => {
  beforeEach(() => {
    mockUseForm.mockReturnValue({
      data: {
        name: 'John Doe',
        email: 'john@example.com',
      },
      setData: vi.fn(),
      patch: vi.fn(),
      errors: {},
      processing: false,
      recentlySuccessful: false,
    });

    mockUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            email_verified_at: '2024-01-01T00:00:00.000000Z',
            updated_at: '2024-01-01T00:00:00.000000Z',
          },
        },
      },
    });

    mockRoute.mockImplementation((name: string) => `/${name.replace('.', '/')}`);
  });

  it('renders profile management page with modern card layout', () => {
    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('Profile Information')).toBeInTheDocument();
    expect(screen.getByText('Update your name and email address. Your email will need to be verified if changed.')).toBeInTheDocument();
    expect(screen.getByText('Account Security')).toBeInTheDocument();
    expect(screen.getByText('Danger Zone')).toBeInTheDocument();
  });

  it('displays profile form fields with current user data', () => {
    render(<Profile mustVerifyEmail={false} />);

    const nameInput = screen.getByDisplayValue('John Doe');
    const emailInput = screen.getByDisplayValue('john@example.com');

    expect(nameInput).toBeInTheDocument();
    expect(emailInput).toBeInTheDocument();
    expect(nameInput).toHaveAttribute('placeholder', 'Full name');
    expect(emailInput).toHaveAttribute('placeholder', 'Email address');
  });

  it('shows email verification warning when email is unverified', () => {
    mockUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            email_verified_at: null, // Unverified
            updated_at: '2024-01-01T00:00:00.000000Z',
          },
        },
      },
    });

    render(<Profile mustVerifyEmail={true} />);

    expect(screen.getByText('Email verification required')).toBeInTheDocument();
    expect(screen.getByText(/Your email address is unverified/)).toBeInTheDocument();
    expect(screen.getByText('Click here to resend the verification email.')).toBeInTheDocument();
  });

  it('displays success message when verification link is sent', () => {
    mockUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            email_verified_at: null,
            updated_at: '2024-01-01T00:00:00.000000Z',
          },
        },
      },
    });

    render(<Profile mustVerifyEmail={true} status="verification-link-sent" />);

    expect(screen.getByText('A new verification link has been sent to your email address.')).toBeInTheDocument();
  });

  it('shows account security section with password change link', () => {
    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('Account Security')).toBeInTheDocument();
    expect(screen.getByText('Manage your password and account security settings.')).toBeInTheDocument();
    expect(screen.getByText('Password')).toBeInTheDocument();
    expect(screen.getByText('Change password')).toBeInTheDocument();
    expect(screen.getByText(/Last updated:/)).toBeInTheDocument();
  });

  it('displays danger zone with delete account component', () => {
    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('Danger Zone')).toBeInTheDocument();
    expect(screen.getByText('Irreversible and destructive actions.')).toBeInTheDocument();
    expect(screen.getByTestId('delete-user')).toBeInTheDocument();
  });

  it('handles form submission', async () => {
    const mockPatch = vi.fn();
    mockUseForm.mockReturnValue({
      data: {
        name: 'John Doe Updated',
        email: 'john.updated@example.com',
      },
      setData: vi.fn(),
      patch: mockPatch,
      errors: {},
      processing: false,
      recentlySuccessful: false,
    });

    render(<Profile mustVerifyEmail={false} />);

    const submitButton = screen.getByRole('button', { name: 'Save changes' });
    fireEvent.click(submitButton);

    expect(mockPatch).toHaveBeenCalledWith('/profile/update', {
      preserveScroll: true,
    });
  });

  it('shows processing state during form submission', () => {
    mockUseForm.mockReturnValue({
      data: {
        name: 'John Doe',
        email: 'john@example.com',
      },
      setData: vi.fn(),
      patch: vi.fn(),
      errors: {},
      processing: true,
      recentlySuccessful: false,
    });

    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('Saving...')).toBeInTheDocument();
  });

  it('shows success message after successful update', () => {
    mockUseForm.mockReturnValue({
      data: {
        name: 'John Doe',
        email: 'john@example.com',
      },
      setData: vi.fn(),
      patch: vi.fn(),
      errors: {},
      processing: false,
      recentlySuccessful: true,
    });

    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('Profile updated successfully!')).toBeInTheDocument();
  });

  it('displays validation errors when present', () => {
    mockUseForm.mockReturnValue({
      data: {
        name: '',
        email: 'invalid-email',
      },
      setData: vi.fn(),
      patch: vi.fn(),
      errors: {
        name: 'The name field is required.',
        email: 'The email must be a valid email address.',
      },
      processing: false,
      recentlySuccessful: false,
    });

    render(<Profile mustVerifyEmail={false} />);

    expect(screen.getByText('The name field is required.')).toBeInTheDocument();
    expect(screen.getByText('The email must be a valid email address.')).toBeInTheDocument();
  });

  it('has proper accessibility attributes', () => {
    render(<Profile mustVerifyEmail={false} />);

    const nameInput = screen.getByLabelText('Name');
    const emailInput = screen.getByLabelText('Email address');

    expect(nameInput).toHaveAttribute('required');
    expect(nameInput).toHaveAttribute('autoComplete', 'name');
    expect(emailInput).toHaveAttribute('required');
    expect(emailInput).toHaveAttribute('autoComplete', 'username');
    expect(emailInput).toHaveAttribute('type', 'email');
  });
});