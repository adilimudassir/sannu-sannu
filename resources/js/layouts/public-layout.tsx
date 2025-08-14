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
            <nav className="bg-card/80 backdrop-blur-md border-b border-border sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16">
                        {/* Logo */}
                        <div className="flex items-center">
                            <Link href={route('home')} className="text-2xl font-bold text-foreground">
                                Sannu-Sannu
                            </Link>
                        </div>

                        {/* Desktop Navigation */}
                        <div className="hidden md:flex items-center space-x-8">
                            <Link 
                                href={route('home')} 
                                className={`transition-colors ${
                                    currentPath === '/' 
                                        ? 'text-foreground font-medium' 
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Home
                            </Link>
                            <Link 
                                href={route('public.projects.index')} 
                                className={`transition-colors ${
                                    currentPath.startsWith('/projects') 
                                        ? 'text-foreground font-medium' 
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Projects
                            </Link>
                            <Link 
                                href={route('home') + '#features'} 
                                className="text-muted-foreground hover:text-foreground transition-colors"
                            >
                                Features
                            </Link>
                            <Link 
                                href={route('home') + '#about'} 
                                className="text-muted-foreground hover:text-foreground transition-colors"
                            >
                                About
                            </Link>
                        </div>

                        {/* Auth Links */}
                        <div className="hidden md:flex items-center space-x-4">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={route('dashboard')}>
                                        Dashboard
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href={route('login')}>
                                            Log in
                                        </Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={route('register')}>
                                            Get Started
                                        </Link>
                                    </Button>
                                </>
                            )}
                        </div>

                        {/* Mobile menu button */}
                        <div className="md:hidden">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            >
                                {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                            </Button>
                        </div>
                    </div>

                    {/* Mobile Navigation */}
                    {mobileMenuOpen && (
                        <div className="md:hidden border-t border-border py-4">
                            <div className="flex flex-col space-y-4">
                                <Link 
                                    href={route('home')} 
                                    className={`transition-colors ${
                                        currentPath === '/' 
                                            ? 'text-foreground font-medium' 
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Home
                                </Link>
                                <Link 
                                    href={route('public.projects.index')} 
                                    className={`transition-colors ${
                                        currentPath.startsWith('/projects') 
                                            ? 'text-foreground font-medium' 
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Projects
                                </Link>
                                <Link 
                                    href={route('home') + '#features'} 
                                    className="text-muted-foreground hover:text-foreground transition-colors"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Features
                                </Link>
                                <Link 
                                    href={route('home') + '#about'} 
                                    className="text-muted-foreground hover:text-foreground transition-colors"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    About
                                </Link>
                                <div className="flex flex-col space-y-2 pt-4 border-t border-border">
                                    {auth.user ? (
                                        <Button asChild className="w-full">
                                            <Link href={route('dashboard')}>
                                                Dashboard
                                            </Link>
                                        </Button>
                                    ) : (
                                        <>
                                            <Button variant="ghost" asChild className="w-full">
                                                <Link href={route('login')}>
                                                    Log in
                                                </Link>
                                            </Button>
                                            <Button asChild className="w-full">
                                                <Link href={route('register')}>
                                                    Get Started
                                                </Link>
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
            <main>
                {children}
            </main>
        </div>
    );
}