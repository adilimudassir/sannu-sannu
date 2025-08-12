import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import Heading from '@/components/heading';
import { AppContent } from '@/components/app-content';

interface Project {
  id: number;
  name: string;
  description: string;
}

interface Props {
  project: Project;
}

export default function ProjectEdit({ project }: Props) {
  const { data, setData, patch, processing, errors } = useForm({
    name: project.name,
    description: project.description,
  });

  function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    patch(route('projects.update', { project: project.id }));
  }

  return (
    <AppLayout>
      <Head title={`Edit ${project.name}`} />
      <AppContent>
        <Heading title="Edit Project" description="Update the details of your project." />
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
                        Update
                    </Button>
                </div>
            </form>
        </div>
      </AppContent>
    </AppLayout>
  );
}
