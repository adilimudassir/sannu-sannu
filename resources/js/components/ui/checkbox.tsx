import * as React from "react"
import * as CheckboxPrimitive from "@radix-ui/react-checkbox"
import { CheckIcon } from "lucide-react"

import { cn } from "@/lib/utils"

function Checkbox({
  className,
  ...props
}: React.ComponentProps<typeof CheckboxPrimitive.Root>) {
  return (
    <CheckboxPrimitive.Root
      data-slot="checkbox"
      className={cn(
        // Base styles with blue theme integration
        "peer border-input size-4 shrink-0 rounded-[4px] border shadow-xs transition-all outline-none disabled:cursor-not-allowed disabled:opacity-50",
        // Blue theme checked states - Modern Sky Blue background
        "data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary",
        // Indeterminate state with blue theme
        "data-[state=indeterminate]:bg-primary data-[state=indeterminate]:text-primary-foreground data-[state=indeterminate]:border-primary",
        // Blue theme focus states - Modern Sky Blue focus ring
        "focus-visible:border-ring focus-visible:ring-ring/30 focus-visible:ring-[3px]",
        // Error states with proper contrast
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        // Hover state for better interactivity
        "hover:border-ring/60 data-[state=checked]:hover:bg-primary/90",
        className
      )}
      {...props}
    >
      <CheckboxPrimitive.Indicator
        data-slot="checkbox-indicator"
        className="flex items-center justify-center text-current transition-opacity"
      >
        <CheckIcon className="size-3.5" />
      </CheckboxPrimitive.Indicator>
    </CheckboxPrimitive.Root>
  )
}

export { Checkbox }
