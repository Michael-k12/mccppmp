<flux:navlist.item icon="home" :href="route('dashboard')" wire:navigate.prefetch>
    Dashboard
</flux:navlist.item>

<flux:navlist.item icon="banknotes" :href="route('budget.index')" wire:navigate>
    Activate Procurement Plan
</flux:navlist.item>

<flux:navlist.item icon="banknotes" :href="route('items.index')" wire:navigate>
   Add Items
</flux:navlist.item>

<flux:navlist.item icon="clipboard-document-check" :href="route('ppmp.principalview')" wire:navigate.prefetch>
    Submitted Project
</flux:navlist.item>

<flux:navlist.item icon="banknotes" :href="route('users.index')" wire:navigate>
   Manage User
</flux:navlist.item>

<flux:navlist.item icon="envelope" :href="route('ppmp.approved')" wire:navigate>
    All Procurement Plan
</flux:navlist.item>
<flux:navlist.item icon="shield-check" :href="route('security.index')" wire:navigate>
    Security Monitoring
</flux:navlist.item>




