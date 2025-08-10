/**
 * Modern Minimalist Blue Theme Constants and Utilities
 * 
 * This module provides TypeScript constants, utilities, and type definitions
 * for the blue theme implementation based on the blue palette guidelines.
 */

// =============================================================================
// BLUE PALETTE CONSTANTS
// =============================================================================

/**
 * Core blue palette colors from the design system
 */
export const BlueTheme = {
  deepNavy: '#0D1B2A',
  royalBlue: '#1B263B', 
  modernSky: '#415A77',
  coolSteel: '#778DA9',
  softIce: '#E0E1DD'
} as const;

/**
 * Extended palette for UI states
 */
export const StateColors = {
  success: '#10B981',
  warning: '#F59E0B', 
  error: '#EF4444',
  info: '#3B82F6'
} as const;

// =============================================================================
// SEMANTIC COLOR MAPPINGS
// =============================================================================

/**
 * Semantic color mappings for consistent component usage
 */
export const SemanticColors = {
  primary: BlueTheme.deepNavy,
  secondary: BlueTheme.royalBlue,
  accent: BlueTheme.modernSky,
  muted: BlueTheme.coolSteel,
  background: BlueTheme.softIce,
  
  // State colors
  success: StateColors.success,
  warning: StateColors.warning,
  error: StateColors.error,
  info: StateColors.info
} as const;

/**
 * Light mode color mappings
 */
export const LightModeColors = {
  primary: BlueTheme.deepNavy,
  secondary: BlueTheme.royalBlue,
  accent: BlueTheme.modernSky,
  muted: BlueTheme.coolSteel,
  background: BlueTheme.softIce,
  surface: '#FFFFFF',
  text: BlueTheme.deepNavy,
  textSecondary: BlueTheme.coolSteel
} as const;

/**
 * Dark mode color mappings with adjusted blue tones
 */
export const DarkModeColors = {
  primary: BlueTheme.modernSky,
  secondary: BlueTheme.royalBlue,
  accent: BlueTheme.modernSky,
  muted: BlueTheme.coolSteel,
  background: BlueTheme.deepNavy,
  surface: BlueTheme.royalBlue,
  text: '#FFFFFF',
  textSecondary: BlueTheme.coolSteel
} as const;

// =============================================================================
// TYPE DEFINITIONS
// =============================================================================

/**
 * Blue theme color keys
 */
export type BlueThemeColor = keyof typeof BlueTheme;

/**
 * State color keys
 */
export type StateColor = keyof typeof StateColors;

/**
 * Semantic color keys
 */
export type SemanticColor = keyof typeof SemanticColors;

/**
 * Theme mode type
 */
export type ThemeMode = 'light' | 'dark';

/**
 * Color variant type for components
 */
export type ColorVariant = 'primary' | 'secondary' | 'accent' | 'muted' | 'success' | 'warning' | 'error' | 'info';

/**
 * Complete theme configuration type
 */
export interface ThemeConfig {
  mode: ThemeMode;
  colors: typeof LightModeColors | typeof DarkModeColors;
  blueTheme: typeof BlueTheme;
  stateColors: typeof StateColors;
}

// =============================================================================
// COLOR UTILITIES
// =============================================================================

/**
 * Get a color value by semantic name
 */
export function getSemanticColor(colorName: SemanticColor): string {
  return SemanticColors[colorName];
}

/**
 * Get a blue theme color by name
 */
export function getBlueThemeColor(colorName: BlueThemeColor): string {
  return BlueTheme[colorName];
}

/**
 * Get a state color by name
 */
export function getStateColor(colorName: StateColor): string {
  return StateColors[colorName];
}

/**
 * Get colors for the specified theme mode
 */
export function getThemeColors(mode: ThemeMode) {
  return mode === 'light' ? LightModeColors : DarkModeColors;
}

/**
 * Convert hex color to RGB values
 */
export function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
}

/**
 * Convert hex color to HSL values
 */
export function hexToHsl(hex: string): { h: number; s: number; l: number } | null {
  const rgb = hexToRgb(hex);
  if (!rgb) return null;

  const { r, g, b } = rgb;
  const rNorm = r / 255;
  const gNorm = g / 255;
  const bNorm = b / 255;

  const max = Math.max(rNorm, gNorm, bNorm);
  const min = Math.min(rNorm, gNorm, bNorm);
  let h = 0;
  let s = 0;
  const l = (max + min) / 2;

  if (max !== min) {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    
    switch (max) {
      case rNorm: h = (gNorm - bNorm) / d + (gNorm < bNorm ? 6 : 0); break;
      case gNorm: h = (bNorm - rNorm) / d + 2; break;
      case bNorm: h = (rNorm - gNorm) / d + 4; break;
    }
    h /= 6;
  }

  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    l: Math.round(l * 100)
  };
}

/**
 * Add alpha transparency to a hex color
 */
export function addAlpha(hex: string, alpha: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;
  
  const clampedAlpha = Math.max(0, Math.min(1, alpha));
  return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${clampedAlpha})`;
}

/**
 * Lighten a hex color by a percentage
 */
export function lightenColor(hex: string, percent: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;

  const factor = 1 + (percent / 100);
  const r = Math.min(255, Math.round(rgb.r * factor));
  const g = Math.min(255, Math.round(rgb.g * factor));
  const b = Math.min(255, Math.round(rgb.b * factor));

  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

/**
 * Darken a hex color by a percentage
 */
export function darkenColor(hex: string, percent: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;

  const factor = 1 - (percent / 100);
  const r = Math.max(0, Math.round(rgb.r * factor));
  const g = Math.max(0, Math.round(rgb.g * factor));
  const b = Math.max(0, Math.round(rgb.b * factor));

  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

/**
 * Get hover state color for a given color
 */
export function getHoverColor(color: string): string {
  // For blue theme colors, use specific hover mappings
  switch (color) {
    case BlueTheme.modernSky:
      return BlueTheme.deepNavy;
    case BlueTheme.deepNavy:
      return darkenColor(color, 10);
    case BlueTheme.royalBlue:
      return darkenColor(color, 15);
    case BlueTheme.coolSteel:
      return darkenColor(color, 20);
    case BlueTheme.softIce:
      return darkenColor(color, 5);
    default:
      return darkenColor(color, 10);
  }
}

/**
 * Get focus ring color with appropriate alpha
 */
export function getFocusRingColor(baseColor: string = BlueTheme.modernSky): string {
  return addAlpha(baseColor, 0.1);
}

/**
 * Validate if a color meets WCAG contrast requirements
 */
export function getContrastRatio(color1: string, color2: string): number {
  const getLuminance = (hex: string): number => {
    const rgb = hexToRgb(hex);
    if (!rgb) return 0;

    const { r, g, b } = rgb;
    const [rNorm, gNorm, bNorm] = [r, g, b].map(c => {
      c = c / 255;
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });

    return 0.2126 * rNorm + 0.7152 * gNorm + 0.0722 * bNorm;
  };

  const lum1 = getLuminance(color1);
  const lum2 = getLuminance(color2);
  const brightest = Math.max(lum1, lum2);
  const darkest = Math.min(lum1, lum2);

  return (brightest + 0.05) / (darkest + 0.05);
}

/**
 * Check if color combination meets WCAG AA standards
 */
export function meetsContrastRequirement(
  foreground: string, 
  background: string, 
  isLargeText: boolean = false
): boolean {
  const ratio = getContrastRatio(foreground, background);
  return isLargeText ? ratio >= 3 : ratio >= 4.5;
}

// =============================================================================
// COMPONENT HELPER UTILITIES
// =============================================================================

/**
 * Get appropriate text color for a background color
 */
export function getTextColorForBackground(backgroundColor: string): string {
  const whiteContrast = getContrastRatio('#FFFFFF', backgroundColor);
  const darkContrast = getContrastRatio(BlueTheme.deepNavy, backgroundColor);
  
  return whiteContrast > darkContrast ? '#FFFFFF' : BlueTheme.deepNavy;
}

/**
 * Generate color variants for a component
 */
export function generateColorVariants(baseColor: string) {
  return {
    base: baseColor,
    hover: getHoverColor(baseColor),
    focus: getFocusRingColor(baseColor),
    light: lightenColor(baseColor, 20),
    dark: darkenColor(baseColor, 20),
    alpha10: addAlpha(baseColor, 0.1),
    alpha20: addAlpha(baseColor, 0.2),
    alpha50: addAlpha(baseColor, 0.5)
  };
}

/**
 * Get color scheme for a specific variant
 */
export function getVariantColorScheme(variant: ColorVariant, mode: ThemeMode = 'light') {
  const themeColors = getThemeColors(mode);
  const baseColor = getSemanticColor(variant);
  
  return {
    primary: baseColor,
    background: mode === 'light' ? themeColors.surface : themeColors.surface,
    text: getTextColorForBackground(baseColor),
    border: addAlpha(baseColor, 0.2),
    hover: getHoverColor(baseColor),
    focus: getFocusRingColor(baseColor)
  };
}

// =============================================================================
// EXPORTS
// =============================================================================

export default {
  BlueTheme,
  StateColors,
  SemanticColors,
  LightModeColors,
  DarkModeColors,
  getSemanticColor,
  getBlueThemeColor,
  getStateColor,
  getThemeColors,
  hexToRgb,
  hexToHsl,
  addAlpha,
  lightenColor,
  darkenColor,
  getHoverColor,
  getFocusRingColor,
  getContrastRatio,
  meetsContrastRequirement,
  getTextColorForBackground,
  generateColorVariants,
  getVariantColorScheme
};