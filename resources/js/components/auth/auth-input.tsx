import * as React from "react"
import { cn } from "@/lib/utils"
import { Input } from "@/components/ui/input"

interface AuthInputProps extends React.ComponentProps<typeof Input> {
  error?: boolean
  "aria-invalid"?: boolean
}

const AuthInput = React.forwardRef<HTMLInputElement, AuthInputProps>(
  ({ className, error, "aria-invalid": ariaInvalid, ...props }, ref) => {
    return (
      <Input
        ref={ref}
        className={cn(
          // Enhanced focus states for better accessibility
          "focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-0",
          // Error states
          error || ariaInvalid ? "border-destructive focus-visible:ring-destructive/20" : "",
          // Improved hover states
          "hover:border-ring/60 transition-all duration-200",
          // Better disabled states
          "disabled:bg-muted disabled:text-muted-foreground disabled:border-muted",
          className
        )}
        aria-invalid={error || ariaInvalid}
        {...props}
      />
    )
  }
)

AuthInput.displayName = "AuthInput"

export { AuthInput }