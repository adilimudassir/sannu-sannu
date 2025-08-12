import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '../../components/ui/textarea';
import InputError from '@/components/input-error';
import Heading from '@/components/heading';
import { AppContent } from '@/components/app-content';

export default function ProjectCreate() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    description: '',
  });

  function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post(route('projects.store'));
  }

  return (
    <AppLayout>
      <Head title="Create Project" />
      <AppContent>
        <Heading title="Create Project" description="Create a new project to start collecting contributions." />
        <div className="max-w-2xl">
            <form onSubmit={submit}>
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setData('name', e.target.value)}
                        required
                    />
                    <InputError message={errors.name} />
                </div>
                <div className="grid gap-2 mt-4">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                        required
                    />
                    <InputError message={errors.description} />
                </div>
                <div className="mt-4">
                    <Button type="submit" disabled={processing}>
                        Create
                    </Button>
                </div>
            </form>
        </div>
      </AppContent>
    </AppLayout>
  );
}
