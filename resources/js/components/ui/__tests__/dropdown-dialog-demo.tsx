import React from 'react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  DropdownMenuCheckboxItem,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
} from '../dropdown-menu';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '../dialog';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '../sheet';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '../tooltip';
import { Button } from '../button';

export function DropdownDialogDemo() {
  const [showDropdown, setShowDropdown] = React.useState(false);
  const [showDialog, setShowDialog] = React.useState(false);
  const [showSheet, setShowSheet] = React.useState(false);
  const [checkedItems, setCheckedItems] = React.useState({
    item1: false,
    item2: true,
  });
  const [radioValue, setRadioValue] = React.useState('option1');

  return (
    <div className="p-8 space-y-8">
      <h2 className="text-2xl font-bold text-foreground mb-6">
        Blue Theme Components Demo
      </h2>

      {/* Dropdown Menu Demo */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold text-foreground">Dropdown Menu</h3>
        <DropdownMenu open={showDropdown} onOpenChange={setShowDropdown}>
          <DropdownMenuTrigger asChild>
            <Button variant="outline">Open Dropdown</Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent className="w-56">
            <DropdownMenuLabel>My Account</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem>Profile</DropdownMenuItem>
            <DropdownMenuItem>Settings</DropdownMenuItem>
            <DropdownMenuItem>Keyboard shortcuts</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
              checked={checkedItems.item1}
              onCheckedChange={(checked) =>
                setCheckedItems(prev => ({ ...prev, item1: checked }))
              }
            >
              Show Notifications
            </DropdownMenuCheckboxItem>
            <DropdownMenuCheckboxItem
              checked={checkedItems.item2}
              onCheckedChange={(checked) =>
                setCheckedItems(prev => ({ ...prev, item2: checked }))
              }
            >
              Show Status Bar
            </DropdownMenuCheckboxItem>
            <DropdownMenuSeparator />
            <DropdownMenuRadioGroup value={radioValue} onValueChange={setRadioValue}>
              <DropdownMenuRadioItem value="option1">Option 1</DropdownMenuRadioItem>
              <DropdownMenuRadioItem value="option2">Option 2</DropdownMenuRadioItem>
              <DropdownMenuRadioItem value="option3">Option 3</DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
            <DropdownMenuSeparator />
            <DropdownMenuSub>
              <DropdownMenuSubTrigger>More Options</DropdownMenuSubTrigger>
              <DropdownMenuSubContent>
                <DropdownMenuItem>Sub Item 1</DropdownMenuItem>
                <DropdownMenuItem>Sub Item 2</DropdownMenuItem>
              </DropdownMenuSubContent>
            </DropdownMenuSub>
            <DropdownMenuSeparator />
            <DropdownMenuItem variant="destructive">
              Delete Account
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      {/* Dialog Demo */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold text-foreground">Dialog</h3>
        <Dialog open={showDialog} onOpenChange={setShowDialog}>
          <DialogTrigger asChild>
            <Button>Open Dialog</Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Blue Theme Dialog</DialogTitle>
              <DialogDescription>
                This dialog demonstrates the blue theme implementation with proper
                contrast ratios and modern styling.
              </DialogDescription>
            </DialogHeader>
            <div className="py-4">
              <p className="text-sm text-muted-foreground">
                The dialog uses the card background color for better contrast
                and includes a backdrop blur effect.
              </p>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowDialog(false)}>
                Cancel
              </Button>
              <Button onClick={() => setShowDialog(false)}>
                Confirm
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      {/* Sheet Demo */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold text-foreground">Sheet</h3>
        <Sheet open={showSheet} onOpenChange={setShowSheet}>
          <SheetTrigger asChild>
            <Button variant="secondary">Open Sheet</Button>
          </SheetTrigger>
          <SheetContent>
            <SheetHeader>
              <SheetTitle>Blue Theme Sheet</SheetTitle>
              <SheetDescription>
                This sheet component uses the blue theme colors for consistent
                styling across the application.
              </SheetDescription>
            </SheetHeader>
            <div className="py-4 space-y-4">
              <div className="space-y-2">
                <h4 className="font-medium text-card-foreground">Features</h4>
                <ul className="text-sm text-muted-foreground space-y-1">
                  <li>• Modern Sky Blue accents</li>
                  <li>• Royal Blue backgrounds</li>
                  <li>• Cool Steel borders</li>
                  <li>• Proper contrast ratios</li>
                </ul>
              </div>
            </div>
          </SheetContent>
        </Sheet>
      </div>

      {/* Tooltip Demo */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold text-foreground">Tooltip</h3>
        <div className="flex gap-4">
          <Tooltip>
            <TooltipTrigger asChild>
              <Button variant="outline">Hover for tooltip</Button>
            </TooltipTrigger>
            <TooltipContent>
              <p>This tooltip uses the blue theme colors</p>
            </TooltipContent>
          </Tooltip>

          <Tooltip>
            <TooltipTrigger asChild>
              <Button>Another tooltip</Button>
            </TooltipTrigger>
            <TooltipContent>
              <p>Consistent styling across all components</p>
            </TooltipContent>
          </Tooltip>
        </div>
      </div>
    </div>
  );
}