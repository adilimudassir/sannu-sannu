import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Menu, X } from 'lucide-react';
import { type SharedData } from '@/types';
import { type PropsWithChildren } from 'react';

export default function PublicLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<SharedData>().props;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    
    // Get current URL safely
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '/';

    return (
        <div className="min-h-screen bg-background">
            {/* Navigation */}
            <nav className="sticky top-0 z-50 border-b border-border bg-card/80 backdrop-blur-md">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 items-center justify-between">
                        {/* Logo */}
                        <div className="flex items-center">
                            <Link href={route('home')} className="text-2xl font-bold text-foreground">
                                Sannu-Sannu
                            </Link>
                        </div>

                        {/* Desktop Navigation */}
                        <div className="hidden items-center space-x-8 md:flex">
                            <Link
                                href={route('home')}
                                className={`transition-colors ${
                                    currentPath === '/' ? 'font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Home
                            </Link>
                            <Link
                                href={route('public.projects.index')}
                                className={`transition-colors ${
                                    currentPath.startsWith('/projects')
                                        ? 'font-medium text-foreground'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Projects
                            </Link>
                            <Link href={route('home') + '#features'} className="text-muted-foreground transition-colors hover:text-foreground">
                                Features
                            </Link>
                            <Link href={route('home') + '#about'} className="text-muted-foreground transition-colors hover:text-foreground">
                                About
                            </Link>
                        </div>

                        {/* Auth Links */}
                        <div className="hidden items-center space-x-4 md:flex">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={route('dashboard')}>Dashboard</Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href={route('login')}>Log in</Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={route('register')}>Get Started</Link>
                                    </Button>
                                </>
                            )}
                        </div>

                        {/* Mobile menu button */}
                        <div className="md:hidden">
                            <Button variant="ghost" size="sm" onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
                                {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                            </Button>
                        </div>
                    </div>

                    {/* Mobile Navigation */}
                    {mobileMenuOpen && (
                        <div className="border-t border-border py-4 md:hidden">
                            <div className="flex flex-col space-y-4">
                                <Link
                                    href={route('home')}
                                    className={`transition-colors ${
                                        currentPath === '/' ? 'font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Home
                                </Link>
                                <Link
                                    href={route('public.projects.index')}
                                    className={`transition-colors ${
                                        currentPath.startsWith('/projects')
                                            ? 'font-medium text-foreground'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Projects
                                </Link>
                                <Link
                                    href={route('home') + '#features'}
                                    className="text-muted-foreground transition-colors hover:text-foreground"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Features
                                </Link>
                                <Link
                                    href={route('home') + '#about'}
                                    className="text-muted-foreground transition-colors hover:text-foreground"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    About
                                </Link>
                                <div className="flex flex-col space-y-2 border-t border-border pt-4">
                                    {auth.user ? (
                                        <Button asChild className="w-full">
                                            <Link href={route('dashboard')}>Dashboard</Link>
                                        </Button>
                                    ) : (
                                        <>
                                            <Button variant="ghost" asChild className="w-full">
                                                <Link href={route('login')}>Log in</Link>
                                            </Button>
                                            <Button asChild className="w-full">
                                                <Link href={route('register')}>Get Started</Link>
                                            </Button>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </nav>

            {/* Main Content */}
            <main>{children}</main>
            {/* Footer */}
            <footer id="about" className="bg-secondary px-4 py-16 text-secondary-foreground sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="grid gap-8 md:grid-cols-4">
                        <div className="md:col-span-2">
                            <h3 className="mb-4 text-2xl font-bold">Sannu-Sannu</h3>
                            <p className="mb-6 max-w-md text-secondary-foreground/80">
                                Building stronger communities through collaborative project funding. Together, we can achieve more than we ever could
                                alone.
                            </p>
                            <div className="flex space-x-4">
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={route('public.projects.index')}>Projects</Link>
                                </Button>
                                {!auth.user && (
                                    <Button size="sm" asChild>
                                        <Link href={route('register')}>Join Now</Link>
                                    </Button>
                                )}
                            </div>
                        </div>

                        <div>
                            <h4 className="mb-4 font-semibold">Platform</h4>
                            <ul className="space-y-2 text-secondary-foreground/70">
                                <li>
                                    <Link href={route('public.projects.index')} className="transition-colors hover:text-secondary-foreground">
                                        Projects
                                    </Link>
                                </li>
                                <li>
                                    <Link href="#" className="transition-colors hover:text-secondary-foreground">
                                        How It Works
                                    </Link>
                                </li>
                                <li>
                                    <Link href="#" className="transition-colors hover:text-secondary-foreground">
                                        Success Stories
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="mb-4 font-semibold">Support</h4>
                            <ul className="space-y-2 text-secondary-foreground/70">
                                <li>
                                    <Link href="#" className="transition-colors hover:text-secondary-foreground">
                                        Help Center
                                    </Link>
                                </li>
                                <li>
                                    <Link href="#" className="transition-colors hover:text-secondary-foreground">
                                        Contact Us
                                    </Link>
                                </li>
                                <li>
                                    <Link href="#" className="transition-colors hover:text-secondary-foreground">
                                        Privacy Policy
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="mt-12 border-t border-secondary-foreground/20 pt-8 text-center text-secondary-foreground/60">
                        <p>&copy; 2025 Sannu-Sannu. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}