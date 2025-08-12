import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const { data, setData, delete: destroy, processing, reset, errors, clearErrors } = useForm<Required<{ password: string }>>({ password: '' });

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        clearErrors();
        reset();
    };

    return (
        <div className="space-y-4">
            <div className="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800/50 dark:bg-red-900/20">
                <div className="flex items-start space-x-3">
                    <div className="flex-shrink-0">
                        <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                    </div>
                    <div className="flex-1">
                        <h3 className="text-sm font-medium text-red-800 dark:text-red-200">
                            Delete Account
                        </h3>
                        <div className="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p>
                                Once you delete your account, all of your data will be permanently removed. 
                                This action cannot be undone.
                            </p>
                        </div>
                        <div className="mt-4">
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button variant="destructive" size="sm">
                                        Delete my account
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
                                    <DialogDescription>
                                        This action cannot be undone. This will permanently delete your account 
                                        and remove all of your data from our servers. Please enter your password 
                                        to confirm.
                                    </DialogDescription>
                                    <form className="space-y-6" onSubmit={deleteUser}>
                                        <div className="grid gap-2">
                                            <Label htmlFor="password">
                                                Confirm your password
                                            </Label>

                                            <Input
                                                id="password"
                                                type="password"
                                                name="password"
                                                ref={passwordInput}
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                placeholder="Enter your password"
                                                autoComplete="current-password"
                                                required
                                            />

                                            <InputError message={errors.password} />
                                        </div>

                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="outline" onClick={closeModal}>
                                                    Cancel
                                                </Button>
                                            </DialogClose>

                                            <Button variant="destructive" disabled={processing} asChild>
                                                <button type="submit">
                                                    {processing ? 'Deleting...' : 'Delete account'}
                                                </button>
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
