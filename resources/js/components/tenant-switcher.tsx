import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Building2, Check, ChevronsUpDown, Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { SharedData, Tenant } from '@/types';

interface TenantSwitcherProps {
  availableTenants?: Array<Tenant & { role: string }>;
  className?: string;
}

export function TenantSwitcher({ availableTenants = [], className }: TenantSwitcherProps) {
  const [open, setOpen] = useState(false);
  const { tenant } = usePage<SharedData>().props;

  const switchTenant = (tenantId: number) => {
    router.post(route('tenant.select.store'), { tenant_id: tenantId });
    setOpen(false);
  };

  const getRoleBadgeVariant = (role: string) => {
    switch (role) {
      case 'tenant_admin':
        return 'default';
      case 'project_manager':
        return 'secondary';
      default:
        return 'outline';
    }
  };

  const getRoleLabel = (role: string) => {
    switch (role) {
      case 'tenant_admin':
        return 'Admin';
      case 'project_manager':
        return 'Manager';
      default:
        return role;
    }
  };

  // If no tenant is selected or no available tenants, show select prompt
  if (!tenant || availableTenants.length === 0) {
    return (
      <Button
        variant="ghost"
        size="sm"
        className={cn(
          "justify-start w-full h-auto px-2 py-1.5",
          "bg-sidebar-accent/50 text-sidebar-accent-foreground",
          "hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
          "border border-sidebar-border",
          className
        )}
        onClick={() => router.visit(route('tenant.select'))}
      >
        <Building2 className="mr-2 h-4 w-4 shrink-0" />
        <span className="truncate text-sm">Select Organization</span>
      </Button>
    );
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="ghost"
          role="combobox"
          aria-expanded={open}
          className={cn(
            "justify-between w-full h-auto px-2 py-1.5",
            "bg-sidebar-accent/50 text-sidebar-accent-foreground",
            "hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
            "border border-sidebar-border",
            className
          )}
        >
          <div className="flex items-center min-w-0">
            <Building2 className="mr-2 h-4 w-4 shrink-0" />
            <span className="truncate text-sm font-medium">{tenant.name}</span>
          </div>
          <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[300px] p-0">
        <Command>
          <CommandInput placeholder="Search organizations..." />
          <CommandList>
            <CommandEmpty>No organizations found.</CommandEmpty>
            <CommandGroup heading="Your Organizations">
              {availableTenants.map((availableTenant) => (
                <CommandItem
                  key={availableTenant.id}
                  value={availableTenant.name}
                  onSelect={() => switchTenant(availableTenant.id)}
                  className="flex items-center justify-between py-2 px-2 cursor-pointer"
                >
                  <div className="flex items-center min-w-0">
                    <Building2 className="mr-2 h-4 w-4 shrink-0 text-muted-foreground" />
                    <div className="min-w-0">
                      <div className="font-medium text-sm truncate">{availableTenant.name}</div>
                      <div className="text-xs text-muted-foreground truncate">@{availableTenant.slug}</div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    <Badge variant={getRoleBadgeVariant(availableTenant.role)} className="text-xs">
                      {getRoleLabel(availableTenant.role)}
                    </Badge>
                    {tenant.id === availableTenant.id && (
                      <Check className="h-4 w-4 text-primary" />
                    )}
                  </div>
                </CommandItem>
              ))}
            </CommandGroup>
            <CommandSeparator />
            <CommandGroup>
              <CommandItem
                onSelect={() => {
                  router.visit(route('tenant.select'));
                  setOpen(false);
                }}
                className="py-2 px-2 cursor-pointer"
              >
                <Plus className="mr-2 h-4 w-4 text-muted-foreground" />
                <span className="text-sm">View All Organizations</span>
              </CommandItem>
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  );
}