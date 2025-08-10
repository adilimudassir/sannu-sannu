import React from 'react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuGroup } from '../dropdown-menu';
import { Button } from '../button';
import { Avatar, AvatarFallback, AvatarImage } from '../avatar';
import { Settings, LogOut, User, CreditCard, Bell, Shield, HelpCircle, ChevronDown } from 'lucide-react';

/**
 * Profile Dropdown Demo Component
 * 
 * This component demonstrates the improved profile dropdown menu
 * with enhanced blue theme styling, better visual hierarchy, and polished appearance.
 */
export function ProfileDropdownDemo() {
  const mockUser = {
    name: 'Mudassir Ahmad',
    email: 'mudassir@example.com',
    avatar: null
  };

  return (
    <div className="p-8 space-y-8 max-w-4xl mx-auto">
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-foreground mb-2">
          Profile Dropdown Menu Demo
        </h1>
        <p className="text-muted-foreground">
          Enhanced dropdown menu with blue theme styling and improved UX
        </p>
      </div>

      <div className="flex flex-wrap gap-8 justify-center">
        {/* Basic Profile Dropdown */}
        <div className="space-y-4">
          <h3 className="font-semibold text-foreground text-center">Basic Profile Menu</h3>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" className="flex items-center gap-2 min-w-48">
                <Avatar className="h-8 w-8">
                  <AvatarImage src={mockUser.avatar} alt={mockUser.name} />
                  <AvatarFallback className="bg-primary text-primary-foreground">
                    MA
                  </AvatarFallback>
                </Avatar>
                <div className="flex-1 text-left">
                  <div className="font-medium">{mockUser.name}</div>
                </div>
                <ChevronDown className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="min-w-64" align="end">
              <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-3 px-2 py-3">
                  <Avatar className="h-10 w-10">
                    <AvatarImage src={mockUser.avatar} alt={mockUser.name} />
                    <AvatarFallback className="bg-primary text-primary-foreground">
                      MA
                    </AvatarFallback>
                  </Avatar>
                  <div className="grid flex-1 text-left text-sm leading-tight">
                    <span className="truncate font-semibold text-foreground">{mockUser.name}</span>
                    <span className="truncate text-xs text-muted-foreground">{mockUser.email}</span>
                  </div>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuItem>
                  <Settings className="size-4 text-muted-foreground" />
                  <span>Settings</span>
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuItem variant="destructive">
                <LogOut className="size-4" />
                <span>Log out</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        {/* Enhanced Profile Dropdown */}
        <div className="space-y-4">
          <h3 className="font-semibold text-foreground text-center">Enhanced Profile Menu</h3>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" className="flex items-center gap-2 min-w-48">
                <Avatar className="h-8 w-8">
                  <AvatarImage src={mockUser.avatar} alt={mockUser.name} />
                  <AvatarFallback className="bg-primary text-primary-foreground">
                    MA
                  </AvatarFallback>
                </Avatar>
                <div className="flex-1 text-left">
                  <div className="font-medium">{mockUser.name}</div>
                </div>
                <ChevronDown className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="min-w-72" align="end">
              <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-3 px-2 py-3">
                  <Avatar className="h-12 w-12">
                    <AvatarImage src={mockUser.avatar} alt={mockUser.name} />
                    <AvatarFallback className="bg-primary text-primary-foreground text-lg">
                      MA
                    </AvatarFallback>
                  </Avatar>
                  <div className="grid flex-1 text-left leading-tight">
                    <span className="truncate font-semibold text-foreground text-base">{mockUser.name}</span>
                    <span className="truncate text-sm text-muted-foreground">{mockUser.email}</span>
                    <span className="truncate text-xs text-muted-foreground/70 mt-1">Free Plan</span>
                  </div>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuItem>
                  <User className="size-4 text-muted-foreground" />
                  <span>Profile</span>
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <Settings className="size-4 text-muted-foreground" />
                  <span>Settings</span>
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <CreditCard className="size-4 text-muted-foreground" />
                  <span>Billing</span>
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <Bell className="size-4 text-muted-foreground" />
                  <span>Notifications</span>
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuItem>
                  <Shield className="size-4 text-muted-foreground" />
                  <span>Privacy & Security</span>
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <HelpCircle className="size-4 text-muted-foreground" />
                  <span>Help & Support</span>
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuItem variant="destructive">
                <LogOut className="size-4" />
                <span>Log out</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      {/* Features Showcase */}
      <div className="mt-12 p-6 bg-muted/30 rounded-xl">
        <h3 className="font-semibold text-foreground mb-4 text-center">Dropdown Menu Improvements</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
          <div className="space-y-2">
            <h4 className="font-medium text-foreground">Visual Enhancements</h4>
            <ul className="text-muted-foreground space-y-1">
              <li>• Rounded corners (rounded-xl)</li>
              <li>• Enhanced shadows (shadow-xl)</li>
              <li>• Better backdrop blur</li>
              <li>• Improved spacing and padding</li>
            </ul>
          </div>
          <div className="space-y-2">
            <h4 className="font-medium text-foreground">Blue Theme Integration</h4>
            <ul className="text-muted-foreground space-y-1">
              <li>• Card background colors</li>
              <li>• Proper text contrast</li>
              <li>• Blue accent colors</li>
              <li>• Consistent color hierarchy</li>
            </ul>
          </div>
          <div className="space-y-2">
            <h4 className="font-medium text-foreground">UX Improvements</h4>
            <ul className="text-muted-foreground space-y-1">
              <li>• Larger touch targets</li>
              <li>• Better hover states</li>
              <li>• Smooth transitions</li>
              <li>• Clear visual hierarchy</li>
            </ul>
          </div>
        </div>
      </div>

      {/* Accessibility Features */}
      <div className="mt-8 p-6 border rounded-xl">
        <h3 className="font-semibold text-foreground mb-4 text-center">Accessibility Features</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          <div className="space-y-2">
            <h4 className="font-medium text-foreground">Keyboard Navigation</h4>
            <ul className="text-muted-foreground space-y-1">
              <li>• Tab to open dropdown</li>
              <li>• Arrow keys to navigate items</li>
              <li>• Enter to select items</li>
              <li>• Escape to close dropdown</li>
            </ul>
          </div>
          <div className="space-y-2">
            <h4 className="font-medium text-foreground">Screen Reader Support</h4>
            <ul className="text-muted-foreground space-y-1">
              <li>• Proper ARIA labels</li>
              <li>• Role attributes</li>
              <li>• State announcements</li>
              <li>• Semantic structure</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}