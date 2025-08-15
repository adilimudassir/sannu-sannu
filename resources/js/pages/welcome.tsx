import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/layouts/public-layout';
import { formatCurrency } from '@/lib/formatters';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Globe, Heart, TrendingUp, Users } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    const features = [
        {
            icon: Globe,
            title: 'Discover Projects',
            description: 'Browse community-driven projects from organizations worldwide',
        },
        {
            icon: Heart,
            title: 'Make a Difference',
            description: 'Support causes you care about with flexible contribution options',
        },
        {
            icon: Users,
            title: 'Build Community',
            description: 'Connect with like-minded people working toward common goals',
        },
        {
            icon: TrendingUp,
            title: 'Track Impact',
            description: 'See real-time progress and the impact of your contributions',
        },
    ];

    const stats = [
        { label: 'Active Projects', value: '150+' },
        { label: 'Community Members', value: '5,000+' },
        { label: 'Funds Raised', value: formatCurrency(2500000) },
        { label: 'Success Rate', value: '94%' },
    ];

    return (
        <PublicLayout>
            <Head title="Sannu-Sannu - Community Project Funding">
                <meta
                    name="description"
                    content="Empowering communities through collaborative project funding. Discover, contribute, and make a difference together."
                />
                <meta name="keywords" content="community funding, project collaboration, crowdfunding, social impact" />
            </Head>

            {/* Hero Section */}
            <section className="px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl text-center">
                    <h1 className="mb-6 text-4xl font-bold text-foreground md:text-6xl">
                        Empower Communities
                        <span className="block text-primary">Through Collaboration</span>
                    </h1>
                    <p className="mx-auto mb-8 max-w-3xl text-xl text-muted-foreground">
                        Sannu-Sannu connects communities with meaningful projects. Discover initiatives, contribute to causes you care about, and
                        watch your impact grow.
                    </p>
                    <div className="flex flex-col justify-center gap-4 sm:flex-row">
                        <Button size="lg" asChild>
                            <Link href={route('public.projects.index')}>
                                Explore Projects
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Link>
                        </Button>
                        {!auth.user && (
                            <Button size="lg" variant="outline" asChild>
                                <Link href={route('register')}>Join Community</Link>
                            </Button>
                        )}
                    </div>
                </div>
            </section>

            {/* Stats Section */}
            <section className="bg-muted/50 px-4 py-16 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
                        {stats.map((stat, index) => (
                            <div key={index} className="text-center">
                                <div className="mb-2 text-3xl font-bold text-primary md:text-4xl">{stat.value}</div>
                                <div className="text-muted-foreground">{stat.label}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-16 text-center">
                        <h2 className="mb-4 text-3xl font-bold text-foreground md:text-4xl">Why Choose Sannu-Sannu?</h2>
                        <p className="mx-auto max-w-2xl text-xl text-muted-foreground">
                            Our platform makes it easy to discover, support, and track meaningful community projects
                        </p>
                    </div>

                    <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        {features.map((feature, index) => (
                            <Card key={index} className="text-center transition-shadow hover:shadow-lg">
                                <CardHeader>
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                        <feature.icon className="h-6 w-6 text-primary" />
                                    </div>
                                    <CardTitle className="text-xl">{feature.title}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <CardDescription className="text-base">{feature.description}</CardDescription>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </section>

            {/* How It Works Section */}
            <section className="bg-muted/50 px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-16 text-center">
                        <h2 className="mb-4 text-3xl font-bold text-foreground md:text-4xl">How It Works</h2>
                        <p className="text-xl text-muted-foreground">Simple steps to start making a difference</p>
                    </div>

                    <div className="grid gap-8 md:grid-cols-3">
                        <div className="text-center">
                            <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground">
                                1
                            </div>
                            <h3 className="mb-4 text-xl font-semibold text-foreground">Discover Projects</h3>
                            <p className="text-muted-foreground">
                                Browse through a variety of community projects and find causes that resonate with you
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground">
                                2
                            </div>
                            <h3 className="mb-4 text-xl font-semibold text-foreground">Make Contributions</h3>
                            <p className="text-muted-foreground">Support projects with flexible payment options that work for your budget</p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground">
                                3
                            </div>
                            <h3 className="mb-4 text-xl font-semibold text-foreground">Track Impact</h3>
                            <p className="text-muted-foreground">Follow project progress and see the real-world impact of your contributions</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-4xl text-center">
                    <h2 className="mb-6 text-3xl font-bold text-foreground md:text-4xl">Ready to Make a Difference?</h2>
                    <p className="mb-8 text-xl text-muted-foreground">Join thousands of community members who are already creating positive change</p>
                    <div className="flex flex-col justify-center gap-4 sm:flex-row">
                        <Button size="lg" asChild>
                            <Link href={route('public.projects.index')}>
                                Start Exploring
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Link>
                        </Button>
                        {!auth.user && (
                            <Button size="lg" variant="outline" asChild>
                                <Link href={route('register')}>Create Account</Link>
                            </Button>
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
