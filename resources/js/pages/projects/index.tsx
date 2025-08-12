import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface Project {
  id: number;
  name: string;
}

interface Props {
  projects: Project[];
}

export default function ProjectIndex({ projects }: Props) {
  return (
    <AppLayout>
      <Head title="Projects" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <h1 className="text-2xl font-bold">Projects</h1>
              <ul>
                {projects.map((project: Project) => (
                  <li key={project.id}>{project.name}</li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
