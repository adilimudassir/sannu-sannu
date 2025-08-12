import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface Project {
  id: number;
  name: string;
  description: string;
}

interface Props {
  project: Project;
}

export default function ProjectShow({ project }: Props) {
  return (
    <AppLayout>
      <Head title={project.name} />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <h1 className="text-2xl font-bold">{project.name}</h1>
              <p className="mt-4">{project.description}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
