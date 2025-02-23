# UI Components Documentation

This document provides an overview of all available UI components and how to use them in your views.

## Table of Contents
- [Form Components](#form-components)
- [Layout Components](#layout-components)
- [Navigation Components](#navigation-components)
- [Feedback Components](#feedback-components)
- [Data Display Components](#data-display-components)

## Form Components

### Input
```blade
<x-form.input 
    name="email" 
    label="Email Address" 
    type="email" 
    :value="old('email')" 
    required />
```

### Select
```blade
<x-form.select 
    name="role" 
    label="Role" 
    :options="['admin' => 'Admin', 'user' => 'User']" 
    :selected="old('role')" />
```

### Textarea
```blade
<x-form.textarea 
    name="description" 
    label="Description" 
    :value="old('description')" 
    rows="4" />
```

### Checkbox
```blade
<x-form.checkbox 
    name="remember" 
    label="Remember me" 
    :checked="old('remember')" />
```

### Form
```blade
<x-form :action="route('users.store')" method="POST">
    <x-slot name="header">
        <h5 class="card-title">Create User</h5>
    </x-slot>
    
    <!-- Form fields here -->
    
    <x-slot name="footer">
        <x-form.button>Submit</x-form.button>
    </x-slot>
</x-form>
```

## Layout Components

### Card
```blade
<x-card>
    <x-slot name="header">Card Title</x-slot>
    Card content here
    <x-slot name="footer">Card footer</x-slot>
</x-card>
```

### Stats Card
```blade
<x-stats-card
    title="Total Users"
    value="1,234"
    icon="bx-user"
    variant="primary"
    :trend="['up', '12%']" />
```

### Page Header
```blade
<x-page-header title="Users">
    <x-slot name="actions">
        <x-form.button>Add User</x-form.button>
    </x-slot>
</x-page-header>
```

## Navigation Components

### Tabs
```blade
<x-tabs active="profile">
    <x-slot name="triggers">
        <x-tab-trigger id="profile" :active="true">Profile</x-tab-trigger>
        <x-tab-trigger id="security">Security</x-tab-trigger>
    </x-slot>
    
    <x-slot name="content">
        <x-tab-panel id="profile" :active="true">
            Profile content
        </x-tab-panel>
        <x-tab-panel id="security">
            Security content
        </x-tab-panel>
    </x-slot>
</x-tabs>
```

### Breadcrumb
```blade
<x-breadcrumb :items="[
    ['title' => 'Home', 'url' => route('home')],
    ['title' => 'Users', 'url' => route('users.index')],
    ['title' => 'Create User']
]" />
```

### Dropdown
```blade
<x-dropdown>
    <x-slot name="trigger">
        <button class="btn btn-primary">Actions</button>
    </x-slot>
    
    <a class="dropdown-item" href="#">Edit</a>
    <a class="dropdown-item" href="#">Delete</a>
</x-dropdown>
```

## Feedback Components

### Alert
```blade
<x-alert type="success" :dismissible="true">
    Operation completed successfully!
</x-alert>
```

### Toast
```blade
<x-toast type="success" title="Success" message="Operation completed!" />
```

### Spinner
```blade
<x-spinner size="lg" variant="primary" />
```

### Empty State
```blade
<x-empty-state
    title="No Data Found"
    message="No records available at the moment."
    icon="bx-folder-open"
    :action-url="route('items.create')"
    action-label="Add Item" />
```

## Data Display Components

### Table
```blade
<x-table :headers="['Name', 'Email', 'Role', 'Actions']">
    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role }}</td>
            <td>
                <x-badge variant="primary">{{ $user->status }}</x-badge>
            </td>
        </tr>
    @endforeach
</x-table>
```

### Badge
```blade
<x-badge variant="success" pill>Active</x-badge>
```

### Activity Log
```blade
<x-activity-log :logs="$activityLogs" />
```

## Modal Components

### Modal
```blade
<x-modal id="createUser" title="Create User">
    <!-- Modal content -->
</x-modal>
```

### Confirmation Modal
```blade
<x-confirm-modal
    id="deleteUser"
    title="Delete User"
    message="Are you sure you want to delete this user?"
    confirm-text="Delete"
    confirm-variant="danger" />
```

## Best Practices

1. Always use components for consistent styling across the application
2. Use appropriate variants for different contexts (e.g., success, danger)
3. Include proper ARIA attributes for accessibility
4. Follow the component documentation for proper usage
5. Customize components using the provided props rather than custom CSS
6. Use slots for complex content within components
7. Leverage the built-in responsive design features

## Customization

Components can be customized in several ways:

1. Props: Use component props for basic customization
2. Slots: Use slots for complex content structure
3. Classes: Add custom classes using the class attribute
4. Styles: Add custom styles using the style attribute
5. Theme: Modify the theme variables in your CSS

## Accessibility

All components are built with accessibility in mind and include:

- Proper ARIA attributes
- Keyboard navigation support
- Focus management
- Screen reader support
- Color contrast compliance

## Browser Support

Components are tested and supported in:

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Contributing

When creating new components or modifying existing ones:

1. Follow the established naming conventions
2. Include proper documentation
3. Add accessibility features
4. Test across different browsers
5. Consider mobile responsiveness
6. Add dark mode support
