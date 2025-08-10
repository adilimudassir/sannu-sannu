# Keyboard Navigation Testing Guide

## Overview

This guide provides step-by-step instructions for manually testing keyboard navigation in the Blue Theme implementation. All interactive elements should be accessible using only the keyboard.

## Testing Environment Setup

### Prerequisites
- Disconnect or disable your mouse/trackpad
- Use only keyboard for navigation
- Test in multiple browsers (Chrome, Firefox, Safari, Edge)
- Test with screen reader if available

### Key Combinations to Know
- **Tab**: Move to next focusable element
- **Shift + Tab**: Move to previous focusable element
- **Enter**: Activate buttons, links, and form submissions
- **Space**: Activate buttons, toggle checkboxes, open dropdowns
- **Arrow Keys**: Navigate within dropdowns, menus, and radio groups
- **Escape**: Close modals, dropdowns, and overlays
- **Home/End**: Jump to first/last item in lists or menus

## Component Testing Checklist

### Button Components

#### Test Steps:
1. **Tab to button**: Focus should be clearly visible with blue ring
2. **Press Enter**: Button should activate
3. **Press Space**: Button should activate
4. **Tab away**: Focus should move to next element

#### Expected Results:
- [ ] Focus indicator is clearly visible (blue ring, 3px width)
- [ ] Both Enter and Space keys activate the button
- [ ] Focus moves logically to next interactive element
- [ ] Disabled buttons cannot receive focus

#### Test All Button Variants:
- [ ] Primary buttons
- [ ] Secondary buttons  
- [ ] Outline buttons
- [ ] Ghost buttons
- [ ] Link buttons
- [ ] Icon buttons

### Form Input Components

#### Text Inputs
1. **Tab to input**: Focus should show blue ring indicator
2. **Type text**: Text should appear normally
3. **Tab away**: Focus should move to next element

#### Expected Results:
- [ ] Focus indicator clearly visible
- [ ] Text input works normally
- [ ] Placeholder text has sufficient contrast
- [ ] Required fields are properly indicated

#### Checkboxes and Radio Buttons
1. **Tab to checkbox/radio**: Focus indicator should appear
2. **Press Space**: Should toggle checkbox or select radio button
3. **Arrow keys** (radio groups): Should move between options

#### Expected Results:
- [ ] Focus indicator visible on checkbox/radio
- [ ] Space key toggles state
- [ ] Arrow keys work for radio button groups
- [ ] Current state is clearly indicated

#### Select Dropdowns
1. **Tab to select**: Focus indicator should appear
2. **Press Space or Enter**: Dropdown should open
3. **Use Arrow keys**: Navigate through options
4. **Press Enter**: Select current option
5. **Press Escape**: Close dropdown without selecting

#### Expected Results:
- [ ] Focus indicator visible on closed select
- [ ] Dropdown opens with Space/Enter
- [ ] Arrow keys navigate options
- [ ] Enter selects option
- [ ] Escape closes dropdown
- [ ] Selected option is clearly indicated

### Navigation Components

#### Main Navigation
1. **Tab through navigation items**: Each should receive focus
2. **Press Enter on nav items**: Should navigate to page/section
3. **Test dropdown menus**: Should open and be navigable

#### Expected Results:
- [ ] All navigation items are focusable
- [ ] Focus order follows visual layout
- [ ] Dropdown menus open and close properly
- [ ] Current page/section is indicated

#### Sidebar Navigation
1. **Tab to sidebar items**: Focus should be visible
2. **Test collapsible sections**: Should expand/collapse
3. **Navigate through all levels**: Focus should work at all levels

#### Expected Results:
- [ ] Sidebar items receive focus
- [ ] Collapsible sections work with keyboard
- [ ] Multi-level navigation is accessible
- [ ] Current location is indicated

### Modal and Dialog Components

#### Modal Testing
1. **Open modal** (via keyboard activation)
2. **Tab within modal**: Focus should stay trapped in modal
3. **Press Escape**: Modal should close
4. **Test close button**: Should be reachable and functional

#### Expected Results:
- [ ] Focus is trapped within modal
- [ ] Tab cycles through modal elements only
- [ ] Escape key closes modal
- [ ] Focus returns to trigger element after closing
- [ ] Close button is keyboard accessible

### Card and Content Components

#### Card Navigation
1. **Tab to interactive elements in cards**: Buttons, links should be focusable
2. **Test card actions**: Any interactive elements should work
3. **Verify focus order**: Should follow logical reading order

#### Expected Results:
- [ ] Interactive elements in cards are focusable
- [ ] Focus order is logical (top to bottom, left to right)
- [ ] Card actions work via keyboard

### Alert and Notification Components

#### Alert Testing
1. **Trigger alerts** (via form validation, actions, etc.)
2. **Tab to dismiss buttons**: Should be focusable if present
3. **Test with screen reader**: Alerts should be announced

#### Expected Results:
- [ ] Alerts are announced by screen readers
- [ ] Dismiss buttons are keyboard accessible
- [ ] Focus management works properly with alerts

## Advanced Testing Scenarios

### Tab Order Testing

#### Test Process:
1. Start at top of page
2. Press Tab repeatedly
3. Note the order of focus movement
4. Verify it follows logical visual order

#### Expected Results:
- [ ] Focus moves left to right, top to bottom
- [ ] No elements are skipped unexpectedly
- [ ] No focus traps (except in modals)
- [ ] Focus is always visible

### Focus Management Testing

#### Page Navigation
1. **Navigate to new page**: Focus should move to appropriate element
2. **Use browser back button**: Focus should be managed appropriately
3. **Test single-page app navigation**: Focus should move to main content

#### Expected Results:
- [ ] Focus moves to main content or first heading on page load
- [ ] Back button navigation maintains logical focus
- [ ] Skip links work properly (if implemented)

### Error Handling Testing

#### Form Validation
1. **Submit form with errors**: Focus should move to first error
2. **Tab through error messages**: Should be reachable
3. **Fix errors and resubmit**: Should work via keyboard

#### Expected Results:
- [ ] Focus moves to first error field
- [ ] Error messages are keyboard accessible
- [ ] Error states are clearly indicated
- [ ] Form can be corrected and submitted via keyboard

## Browser-Specific Testing

### Chrome/Chromium
- [ ] Tab navigation works correctly
- [ ] Focus indicators are visible
- [ ] All interactive elements are reachable

### Firefox
- [ ] Tab navigation works correctly
- [ ] Focus indicators are visible
- [ ] All interactive elements are reachable

### Safari
- [ ] Tab navigation works correctly (may need to enable in preferences)
- [ ] Focus indicators are visible
- [ ] All interactive elements are reachable

### Edge
- [ ] Tab navigation works correctly
- [ ] Focus indicators are visible
- [ ] All interactive elements are reachable

## Common Issues to Watch For

### Focus Indicators
- [ ] Focus indicators are clearly visible
- [ ] Focus indicators have sufficient contrast
- [ ] Focus indicators are not cut off by containers
- [ ] Focus indicators work in high contrast mode

### Tab Order Issues
- [ ] No elements are skipped in tab order
- [ ] Tab order follows visual layout
- [ ] Hidden elements don't receive focus
- [ ] Disabled elements don't receive focus

### Keyboard Traps
- [ ] Focus doesn't get stuck in any component (except modals)
- [ ] All focusable elements can be reached
- [ ] User can always navigate away from any element

### Missing Functionality
- [ ] All mouse interactions have keyboard equivalents
- [ ] Hover states have focus equivalents
- [ ] Click actions work with Enter/Space keys

## Reporting Issues

### Issue Documentation Template
```
**Component**: [Button/Input/Modal/etc.]
**Browser**: [Chrome/Firefox/Safari/Edge]
**Issue**: [Brief description]
**Steps to Reproduce**:
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected Behavior**: [What should happen]
**Actual Behavior**: [What actually happens]
**Severity**: [High/Medium/Low]
```

### Priority Levels
- **High**: Functionality completely inaccessible via keyboard
- **Medium**: Functionality accessible but difficult or confusing
- **Low**: Minor usability issues or improvements

## Testing Frequency

### During Development
- Test each new component as it's built
- Test modified components after changes
- Run full keyboard navigation test before releases

### Regular Maintenance
- Monthly spot checks on key user flows
- Quarterly comprehensive testing
- After major browser updates
- When new components are added

## Resources and Tools

### Browser Developer Tools
- Use browser dev tools to inspect focus states
- Check for proper ARIA attributes
- Verify tab order in DOM

### Screen Reader Testing
- Test with VoiceOver (macOS)
- Test with NVDA (Windows)
- Test with JAWS (Windows)

### Automated Tools
- Run accessibility audit script: `node scripts/accessibility-audit.js`
- Run component tests: `npm test accessibility-compliance.test.tsx`

---

**Last Updated**: August 10, 2025  
**Next Review**: November 10, 2025  
**Maintained By**: Development Team