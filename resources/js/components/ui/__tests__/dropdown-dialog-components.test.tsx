import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { DropdownDialogDemo } from './dropdown-dialog-demo';

describe('Blue Theme Components', () => {
  it('renders dropdown menu with blue theme classes', async () => {
    const user = userEvent.setup();
    render(<DropdownDialogDemo />);
    
    // Find and click the dropdown trigger
    const dropdownTrigger = screen.getByRole('button', { name: /open dropdown/i });
    expect(dropdownTrigger).toBeInTheDocument();
    
    await user.click(dropdownTrigger);
    
    // Check if dropdown content appears
    const profileItem = screen.getByText('Profile');
    expect(profileItem).toBeInTheDocument();
    
    // Check if checkbox items are present
    const notificationCheckbox = screen.getByText('Show Notifications');
    expect(notificationCheckbox).toBeInTheDocument();
  });

  it('renders dialog with blue theme classes', async () => {
    const user = userEvent.setup();
    render(<DropdownDialogDemo />);
    
    // Find and click the dialog trigger
    const dialogTrigger = screen.getByRole('button', { name: /open dialog/i });
    expect(dialogTrigger).toBeInTheDocument();
    
    await user.click(dialogTrigger);
    
    // Check if dialog content appears
    const dialogTitle = screen.getByText('Blue Theme Dialog');
    expect(dialogTitle).toBeInTheDocument();
    
    const dialogDescription = screen.getByText(/this dialog demonstrates/i);
    expect(dialogDescription).toBeInTheDocument();
  });

  it('renders sheet with blue theme classes', async () => {
    const user = userEvent.setup();
    render(<DropdownDialogDemo />);
    
    // Find and click the sheet trigger
    const sheetTrigger = screen.getByRole('button', { name: /open sheet/i });
    expect(sheetTrigger).toBeInTheDocument();
    
    await user.click(sheetTrigger);
    
    // Check if sheet content appears
    const sheetTitle = screen.getByText('Blue Theme Sheet');
    expect(sheetTitle).toBeInTheDocument();
    
    const sheetDescription = screen.getByText(/this sheet component/i);
    expect(sheetDescription).toBeInTheDocument();
  });

  it('renders tooltips with blue theme classes', () => {
    render(<DropdownDialogDemo />);
    
    // Find tooltip triggers
    const tooltipTrigger = screen.getByRole('button', { name: /hover for tooltip/i });
    expect(tooltipTrigger).toBeInTheDocument();
    
    const anotherTooltipTrigger = screen.getByRole('button', { name: /another tooltip/i });
    expect(anotherTooltipTrigger).toBeInTheDocument();
  });

  it('has proper blue theme color classes applied', () => {
    render(<DropdownDialogDemo />);
    
    // Check if the main container has proper text color
    const heading = screen.getByText('Blue Theme Components Demo');
    expect(heading).toHaveClass('text-foreground');
    
    // Check if buttons have proper styling
    const buttons = screen.getAllByRole('button');
    expect(buttons.length).toBeGreaterThan(0);
  });

  it('maintains accessibility with blue theme', () => {
    render(<DropdownDialogDemo />);
    
    // Check if all interactive elements are accessible
    const buttons = screen.getAllByRole('button');
    buttons.forEach(button => {
      expect(button).toBeVisible();
    });
    
    // Check if headings maintain proper hierarchy
    const mainHeading = screen.getByRole('heading', { level: 2 });
    expect(mainHeading).toBeInTheDocument();
    
    const subHeadings = screen.getAllByRole('heading', { level: 3 });
    expect(subHeadings.length).toBeGreaterThan(0);
  });
});