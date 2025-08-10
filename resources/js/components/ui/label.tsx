import * as React from "react"
import * as LabelPrimitive from "@radix-ui/react-label"

import { cn } from "@/lib/utils"

function Label({
  className,
  ...props
}: React.ComponentProps<typeof LabelPrimitive.Root>) {
  return (
    <LabelPrimitive.Root
      data-slot="label"
      className={cn(
        // Base styles with blue theme text colors
        "text-sm leading-none font-medium select-none text-foreground",
        // Disabled states with proper opacity
        "group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50",
        // Error state styling
        "group-aria-invalid:text-destructive peer-aria-invalid:text-destructive",
        // Required indicator styling (for labels with required fields)
        "[&[data-required]]:after:content-['*'] [&[data-required]]:after:ml-1 [&[data-required]]:after:text-destructive",
        className
      )}
      {...props}
    />
  )
}

export { Label }
