import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { router } from '@inertiajs/react';
import VerifyEmail from '../verify-email';

// Mock Inertia router
jest.mock('@inertiajs/react', () => ({
    ...jest.requireActual('@inertiajs/react'),
    router: {
        post: jest.fn(),
    },
    Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
    useForm: () => ({
        post: jest.fn(),
        processing: false,
    }),
}));

// Mock route helper
global.route = jest.fn((name: string) => {
    const routes: Record<string, string> = {
        'verification.send': '/email/verification-notification',
        'logout': '/logout',
    };
    return routes[name] || '/';
});

describe('VerifyEmail', () => {
    const defaultProps = {
        user: {
            name: 'John Doe',
            email: 'john@example.com',
        },
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('renders the email verification page correctly', () => {
        render(<VerifyEmail {...defaultProps} />);

        expect(screen.getByText('Verify Your Email Address')).toBeInTheDocument();
        expect(screen.getByText('john@example.com')).toBeInTheDocument();
        expect(screen.getByText('Verification email sent to:')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /resend verification email/i })).toBeInTheDocument();
    });

    it('displays success message when verification link is sent', () => {
        render(<VerifyEmail {...defaultProps} status="verification-link-sent" />);

        expect(screen.getByText('A new verification link has been sent to your email address.')).toBeInTheDocument();
    });

    it('displays throttling message when requests are throttled', () => {
        render(<VerifyEmail {...defaultProps} status="verification-throttled" />);

        expect(screen.getByText(/please wait.*seconds before requesting another verification email/i)).toBeInTheDocument();
    });

    it('disables resend button when processing', () => {
        const mockUseForm = jest.fn(() => ({
            post: jest.fn(),
            processing: true,
        }));

        // Mock useForm to return processing state
        jest.doMock('@inertiajs/react', () => ({
            ...jest.requireActual('@inertiajs/react'),
            useForm: mockUseForm,
            Head: ({ children }: { children: React.ReactNode }) => <>{children}</>,
        }));

        render(<VerifyEmail {...defaultProps} />);

        const button = screen.getByRole('button', { name: /sending/i });
        expect(button).toBeDisabled();
    });

    it('shows helpful instructions and tips', () => {
        render(<VerifyEmail {...defaultProps} />);

        expect(screen.getByText(/didn't receive the email\?/i)).toBeInTheDocument();
        expect(screen.getByText(/verification links expire after 60 minutes/i)).toBeInTheDocument();
        expect(screen.getByText(/you can request a new link if the current one expires/i)).toBeInTheDocument();
    });

    it('provides logout option', () => {
        render(<VerifyEmail {...defaultProps} />);

        const logoutLink = screen.getByText(/sign out and use a different account/i);
        expect(logoutLink).toBeInTheDocument();
        expect(logoutLink.closest('a')).toHaveAttribute('href', '/logout');
    });

    it('shows contact support information', () => {
        render(<VerifyEmail {...defaultProps} />);

        expect(screen.getByText(/having trouble\? contact support for assistance/i)).toBeInTheDocument();
    });

    it('handles countdown timer for throttled requests', async () => {
        // This test would require more complex mocking of the timer functionality
        // For now, we'll just verify the component renders with throttled status
        render(<VerifyEmail {...defaultProps} status="verification-throttled" />);

        expect(screen.getByText(/please wait.*before requesting another verification email/i)).toBeInTheDocument();
    });

    it('is accessible with proper ARIA labels and structure', () => {
        render(<VerifyEmail {...defaultProps} />);

        // Check for proper heading structure
        expect(screen.getByRole('heading', { name: /verify your email address/i })).toBeInTheDocument();
        
        // Check for form elements
        expect(screen.getByRole('button', { name: /resend verification email/i })).toBeInTheDocument();
        
        // Check for proper link structure
        const logoutLink = screen.getByRole('link', { name: /sign out and use a different account/i });
        expect(logoutLink).toBeInTheDocument();
    });

    it('displays user email in a visually distinct way', () => {
        render(<VerifyEmail {...defaultProps} />);

        const emailElement = screen.getByText('john@example.com');
        expect(emailElement).toHaveClass('font-medium');
    });
});