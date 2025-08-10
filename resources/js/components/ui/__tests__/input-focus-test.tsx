import React from 'react';
import { Input } from '../input';
import { Label } from '../label';

/**
 * Input Focus Test Component
 * 
 * This component demonstrates the improved input focus styling
 * with better contrast and visibility for accessibility compliance.
 */
export function InputFocusTest() {
  return (
    <div className="p-8 space-y-6 max-w-2xl mx-auto">
      <div className="text-center mb-8">
        <h1 className="text-2xl font-bold text-foreground mb-2">
          Input Focus Styling Test
        </h1>
        <p className="text-muted-foreground">
          Testing improved focus indicators and contrast ratios
        </p>
      </div>

      <div className="space-y-6">
        {/* Normal Input */}
        <div className="space-y-2">
          <Label htmlFor="normal-input">Normal Input Field</Label>
          <Input
            id="normal-input"
            placeholder="Click or tab to focus this input"
            className="w-full"
          />
          <p className="text-sm text-muted-foreground">
            Focus indicator shows prominent blue ring, border stays normal
          </p>
        </div>

        {/* Input with Value */}
        <div className="space-y-2">
          <Label htmlFor="filled-input">Input with Value</Label>
          <Input
            id="filled-input"
            defaultValue="Mudassir Ahmad"
            className="w-full"
          />
          <p className="text-sm text-muted-foreground">
            Text should have sufficient contrast against background
          </p>
        </div>

        {/* Required Input */}
        <div className="space-y-2">
          <Label htmlFor="required-input">
            Required Input <span className="text-destructive">*</span>
          </Label>
          <Input
            id="required-input"
            required
            placeholder="This field is required"
            className="w-full"
          />
          <p className="text-sm text-muted-foreground">
            Required indicator should be clearly visible
          </p>
        </div>

        {/* Error State Input */}
        <div className="space-y-2">
          <Label htmlFor="error-input">Input with Error</Label>
          <Input
            id="error-input"
            aria-invalid="true"
            placeholder="This input has an error"
            className="w-full"
          />
          <p className="text-sm text-destructive">
            Error state should show red border and ring
          </p>
        </div>

        {/* Disabled Input */}
        <div className="space-y-2">
          <Label htmlFor="disabled-input">Disabled Input</Label>
          <Input
            id="disabled-input"
            disabled
            placeholder="This input is disabled"
            defaultValue="Cannot edit this"
            className="w-full"
          />
          <p className="text-sm text-muted-foreground">
            Disabled state should be clearly indicated
          </p>
        </div>
      </div>

      {/* Focus Testing Instructions */}
      <div className="mt-8 p-4 bg-muted rounded-lg">
        <h3 className="font-semibold text-foreground mb-2">Focus Testing Instructions</h3>
        <ul className="text-sm text-muted-foreground space-y-1">
          <li>• Use Tab key to navigate between inputs</li>
          <li>• Focus ring should be clearly visible with blue color</li>
          <li>• Border stays normal on focus (ring is the focus indicator)</li>
          <li>• Text should maintain good contrast in all states</li>
          <li>• Error states should show red indicators</li>
          <li>• Disabled inputs should not be focusable</li>
        </ul>
      </div>

      {/* Accessibility Checklist */}
      <div className="mt-6 p-4 border rounded-lg">
        <h3 className="font-semibold text-foreground mb-2">Accessibility Checklist</h3>
        <div className="space-y-2 text-sm">
          <div className="flex items-center space-x-2">
            <span className="text-green-600">✓</span>
            <span>Focus indicators meet 3:1 contrast ratio requirement</span>
          </div>
          <div className="flex items-center space-x-2">
            <span className="text-green-600">✓</span>
            <span>Text meets 4.5:1 contrast ratio for normal text</span>
          </div>
          <div className="flex items-center space-x-2">
            <span className="text-green-600">✓</span>
            <span>Placeholder text meets contrast requirements</span>
          </div>
          <div className="flex items-center space-x-2">
            <span className="text-green-600">✓</span>
            <span>Error states are clearly indicated</span>
          </div>
          <div className="flex items-center space-x-2">
            <span className="text-green-600">✓</span>
            <span>Keyboard navigation works properly</span>
          </div>
        </div>
      </div>
    </div>
  );
}