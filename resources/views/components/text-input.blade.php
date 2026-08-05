@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-border bg-surface-muted focus:border-accent focus:ring-accent rounded-md shadow-sm text-text-primary']) }}>
