import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    slug?: string;
    message?: string;
}

export default function TenantNotFound({ slug, message }: Props) {
    return (
        <>
            <Head title="Organization Not Found" />
            
            <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <CardTitle className="text-2xl font-bold text-gray-900">
                            Organization Not Found
                        </CardTitle>
                        <CardDescription>
                            {message || 'The organization you are looking for could not be found.'}
                        </CardDescription>
                    </CardHeader>
                    
                    <CardContent className="space-y-4">
                        {slug && (
                            <div className="bg-gray-100 rounded-md p-3 text-center">
                                <p className="text-sm text-gray-600">
                                    Looking for: <span className="font-mono font-medium">{slug}</span>
                                </p>
                            </div>
                        )}
                        
                        <div className="space-y-3">
                            <p className="text-sm text-gray-600 text-center">
                                This could happen if:
                            </p>
                            <ul className="text-sm text-gray-600 space-y-1 list-disc list-inside">
                                <li>The organization name was typed incorrectly</li>
                                <li>The organization has been deactivated</li>
                                <li>You don't have access to this organization</li>
                            </ul>
                        </div>
                        
                        <div className="flex flex-col space-y-2">
                            <Button asChild className="w-full">
                                <Link href="/">
                                    Go to Homepage
                                </Link>
                            </Button>
                            
                            <Button variant="outline" asChild className="w-full">
                                <a href="mailto:support@sannu-sannu.com">
                                    Contact Support
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}