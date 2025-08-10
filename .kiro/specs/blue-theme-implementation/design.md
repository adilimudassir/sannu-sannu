# Design Document

## Overview

This design implements a modern minimalist blue theme system for the React/Laravel application using Tailwind CSS v4's new CSS-based configuration approach. The implementation will leverage CSS custom properties and the `@theme` directive to define the blue palette colors, ensuring consistent theming across all components while maintaining accessibility and supporting both light and dark modes.

**Reference Document**: #[[file:blue_palette_guidelines.md]] - This comprehensive design system document provides the complete color palette, usage guidelines, component specifications, and implementation examples that will guide this implementation.

## Architecture

### Theme Configuration Approach

Since Tailwind CSS v4 eliminates the traditional config file, the theme will be configured directly in the CSS using the `@theme` directive. This approach provides:

- Direct CSS custom property definitions
- Better performance through native CSS variables
- Simplified theme switching for light/dark modes
- Seamless integration with existing component structure

### Color System Hierarchy

The blue palette from #[[file:blue_palette_guidelines.md]] will be mapped to semantic color roles:

- **Primary**: Deep Navy (#0D1B2A) - Main brand color, headers, primary actions
- **Secondary**: Royal Blue (#1B263B) - Navigation, section backgrounds  
- **Accent**: Modern Sky Blue (#415A77) - Interactive elements, highlights
- **Muted**: Cool Steel Blue (#778DA9) - Borders, secondary text, icons
- **Background**: Soft Ice Blue (#E0E1DD) - Page backgrounds, neutral spaces

Additional semantic colors for UI states (as defined in the guidelines):
- **Success**: Green (#10B981) - Success messages, completed states
- **Warning**: Amber (#F59E0B) - Warning alerts, pending states
- **Error**: Red (#EF4444) - Error messages, destructive actions
- **Info**: Blue (#3B82F6) - Information alerts, neutral actions

## Components and Interfaces

### CSS Theme Structure

The theme will be implemented through CSS custom properties in `resources/css/app.css` following the specifications in #[[file:blue_palette_guidelines.md]]:

```css
@theme {
  /* Blue Palette Colors - from blue_palette_guidelines.md */
  --color-deep-navy: #0D1B2A;
  --color-royal-blue: #1B263B;
  --color-modern-sky: #415A77;
  --color-cool-steel: #778DA9;
  --color-soft-ice: #E0E1DD;
  
  /* Extended Palette for UI States */
  --color-success: #10B981;
  --color-warning: #F59E0B;
  --color-error: #EF4444;
  --color-info: #3B82F6;
  
  /* Semantic Color Mapping */
  --color-primary: var(--deep-navy);
  --color-secondary: var(--royal-blue);
  --color-accent: var(--modern-sky);
  --color-muted: var(--cool-steel);
  --color-background: var(--soft-ice);
}
```

### Component Integration Points

1. **UI Components** (`resources/js/components/ui/`)
   - Button variants will use the blue palette for different states
   - Card components will use appropriate background and border colors
   - Form elements will adopt blue focus states and validation colors

2. **Layout Components** (`resources/js/components/`)
   - App shell and sidebar will use the hierarchical blue system
   - Navigation components will implement the secondary blue tones
   - Header components will use the primary deep navy

3. **Utility Classes**
   - Tailwind will automatically generate utility classes for the new colors
   - Custom utilities for blue-specific patterns if needed

## Data Models

### Color Definitions

```typescript
// Color palette constants for TypeScript usage
export const BlueTheme = {
  deepNavy: '#0D1B2A',
  royalBlue: '#1B263B',
  modernSky: '#415A77',
  coolSteel: '#778DA9',
  softIce: '#E0E1DD'
} as const;

// Semantic color mapping
export const SemanticColors = {
  primary: BlueTheme.deepNavy,
  secondary: BlueTheme.royalBlue,
  accent: BlueTheme.modernSky,
  muted: BlueTheme.coolSteel,
  background: BlueTheme.softIce
} as const;
```

### Theme Mode Support

The design will support light and dark modes by adjusting the CSS custom properties:

```css
:root {
  /* Light mode blue theme */
  --primary: var(--deep-navy);
  --background: var(--soft-ice);
  /* ... other light mode mappings */
}

.dark {
  /* Dark mode blue theme with adjusted values */
  --primary: var(--modern-sky);
  --background: var(--deep-navy);
  /* ... other dark mode mappings */
}
```

## Error Handling

### Fallback Colors

- Each color definition will include fallback values for older browsers
- Graceful degradation for browsers that don't support CSS custom properties
- Default color values in case theme loading fails

### Accessibility Compliance

- All color combinations will be validated against WCAG AA contrast requirements
- Focus indicators will maintain sufficient contrast ratios
- Color-blind friendly palette verification

## Testing Strategy

### Visual Regression Testing

1. **Component Testing**
   - Screenshot comparisons for all UI components with new theme
   - Cross-browser compatibility testing
   - Responsive design validation

2. **Accessibility Testing**
   - Automated contrast ratio validation
   - Screen reader compatibility testing
   - Keyboard navigation verification

3. **Integration Testing**
   - Theme switching functionality
   - Component state preservation during theme changes
   - Performance impact assessment

### Manual Testing Checklist

- [ ] All components render correctly with blue theme
- [ ] Light/dark mode switching works seamlessly
- [ ] Interactive states (hover, focus, active) use appropriate blue variants
- [ ] Text remains readable across all color combinations
- [ ] Navigation and sidebar hierarchy is visually clear

## Implementation Phases

### Phase 1: Core Theme Setup
- Update CSS with blue palette definitions
- Map semantic colors to blue palette
- Test basic color application

### Phase 2: Component Updates
- Update UI components to use new color system
- Implement proper contrast ratios
- Add dark mode support

### Phase 3: Layout Integration
- Apply theme to app shell and navigation
- Update sidebar and header components
- Ensure responsive behavior

### Phase 4: Testing and Refinement
- Comprehensive accessibility testing
- Visual regression testing
- Performance optimization
- Final adjustments based on testing results