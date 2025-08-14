import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type ProjectStatus = 'draft' | 'active' | 'paused' | 'completed' | 'cancelled';

interface ProjectStatusBadgeProps {
  status: ProjectStatus;
  className?: string;
}

const statusConfig = {
  draft: { 
    label: 'Draft', 
    variant: 'secondary' as const, 
    icon: '📝',
    className: 'bg-gray-100 text-gray-800 hover:bg-gray-200'
  },
  active: { 
    label: 'Active', 
    variant: 'default' as const, 
    icon: '🟢',
    className: 'bg-green-100 text-green-800 hover:bg-green-200'
  },
  paused: { 
    label: 'Paused', 
    variant: 'outline' as const, 
    icon: '⏸️',
    className: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
  },
  completed: { 
    label: 'Completed', 
    variant: 'secondary' as const, 
    icon: '✅',
    className: 'bg-blue-100 text-blue-800 hover:bg-blue-200'
  },
  cancelled: { 
    label: 'Cancelled', 
    variant: 'destructive' as const, 
    icon: '❌',
    className: 'bg-red-100 text-red-800 hover:bg-red-200'
  },
};

export default function ProjectStatusBadge({ status, className }: ProjectStatusBadgeProps) {
  const config = statusConfig[status];
  
  return (
    <Badge 
      variant={config.variant} 
      className={cn('gap-1 font-medium', config.className, className)}
    >
      <span className="text-xs">{config.icon}</span>
      {config.label}
    </Badge>
  );
}